import re
import os
import mysql.connector
import pygame
import tempfile
import time
import random
import pickle
import dateparser
import speech_recognition as sr
import sys
from gtts import gTTS
from datetime import datetime, timedelta

DEBUG = True  # Aktifkan debug mode untuk melihat parsing waktu

phonetic_dict = {
    'A': 'Alpha',   'B': 'Bravo',   'C': 'Charlie',
    'D': 'Delta',   'E': 'Echo',    'F': 'Foxtrot',
    'G': 'Golf',    'H': 'Hotel',   'I': 'India',
    'J': 'Juliett', 'K': 'Kilo',    'L': 'Lima',
    'M': 'Mike',    'N': 'November','O': 'Oscar',
    'P': 'Papa',    'Q': 'Quebec',  'R': 'Romeo',
    'S': 'Sierra',  'T': 'Tango',   'U': 'Uniform',
    'V': 'Victor',  'W': 'Whiskey', 'X': 'X-ray',
    'Y': 'Yankee',  'Z': 'Zulu'
}

# === Kamus koreksi kata-kata ASR yang sering salah ===
correction_dict = {
    "korek": "correct",
    "forex": "correct",
    "forever": "correct over",
    "bts": "vts",
    "opor" : "over",
    "obor" : "over",
    "odor" : "over",
    "uber" : "over",
    "umur" : "over",
    "rubber" : "over",
    "lebar" : "over",
    "cover" : "over",
    "oper" : "over",
    "ower" : "over",
    "nomor" : "over",
    "google" : "over",
    "o" : "over",
    "m" : "meter",
    "shallower" : "salah over",
    "salaman" : "salah",
    "sholawat" : "salah",
    "sarah" : "salah",
    # tambahkan sesuai pengalaman error ASR-mu
}

# === Koneksi database ===
def connect_db():
    try:
        return mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="update_ai"
        )
    except mysql.connector.Error as err:
        print(f"Error koneksi database: {err}")
        speak("Maaf kept, sistem tidak bisa terhubung ke database saat ini.")
        sys.exit()

def convert_to_phonetic(text):
    # Ubah singkatan kapital (ABC) ke Alpha Bravo Charlie
    def replace_caps(match):
        chars = match.group(0)
        return ' '.join(phonetic_dict.get(c.upper(), c) for c in chars)

    # Hanya ubah kata yang seluruhnya huruf kapital dan panjang 2-5 huruf
    return re.sub(r'\b[A-Z]{2,4}\b', replace_caps, text)

# === Fungsi Text to Speech ===
def speak(text):
    if not text or not text.strip():
        print("[WARNING] Teks kosong diberikan ke speak(), dilewati.")
        return
    try:
        text = convert_to_phonetic(text)
        tts = gTTS(text=text, lang='id')
        with tempfile.NamedTemporaryFile(delete=False, suffix=".mp3") as temp_audio:
            tts.save(temp_audio.name)
            temp_name = temp_audio.name
        if not pygame.mixer.get_init():
            pygame.mixer.init()
        pygame.mixer.music.load(temp_name)
        pygame.mixer.music.play()
        while pygame.mixer.music.get_busy():
            time.sleep(0.3)
        pygame.mixer.music.unload()
        os.remove(temp_name)
    except Exception as e:
        print(f"[ERROR] Speak failed: {e}")

def correct_text(text):
    if not text:
        return text
    words = text.split()
    corrected_words = [correction_dict.get(word.lower(), word) for word in words]
    return ' '.join(corrected_words)

# === Fungsi untuk Speech to Text (STT) ===
def get_voice_input(prompt=""):
    print(f"AI (dengarkan): {prompt}")
    speak(prompt)
    log_chat("AI", prompt)
    r = sr.Recognizer()
    with sr.Microphone() as source:
        r.adjust_for_ambient_noise(source, duration=1)  # sesuaikan dengan noise
        print("Mendengarkan...")
        audio = r.listen(source, timeout=5, phrase_time_limit=10)  # timeout penting
    try:
        text = r.recognize_google(audio, language="id-ID")
        text = correct_text(text)  # <-- Koreksi teks setelah ASR
        print(f"Kapten (suara): {text}")
        log_chat("Kapten", text)
        return text
    except sr.UnknownValueError:
        speak("Maaf kept, suara Anda tidak terdengar jelas, mohon ulangi.")
        return get_voice_input(prompt)
    except sr.RequestError:
        speak("Maaf kept, ada gangguan teknis dalam mendengar suara Anda.")
        return ""

def log_chat(role, message):
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    log_line = f"[{timestamp}] {role}: {message}\n"
    with open("chat_log.txt", "a", encoding="utf-8") as f:
        f.write(log_line)

def get_next_id(data_monitor):
    db = connect_db()
    cursor = db.cursor()
    try:
        cursor.execute(f"SELECT MAX(id_monitor) FROM {data_monitor}")
        result = cursor.fetchone()
        return (result[0] or 0) + 1
    except mysql.connector.Error as err:
        print(f"Error saat ambil id terakhir: {err}")
        return 1
    finally:
        cursor.close()
        db.close()

def input_identitas(identitas, id_petugas, latitude, longitude):
    db = connect_db()
    cursor = db.cursor()
    try:
        # Cek apakah identitas adalah ID AGEN
        cursor.execute("SELECT MMSI FROM data_agen WHERE id_agen = %s", (identitas,))
        result = cursor.fetchone()

        if not result:
            print("[ERROR] Input bukan ID Agen yang valid.")
            speak("Maaf capt, ID agen tidak ditemukan dalam sistem.")
            return None, None, None, None

        # Jika ID agen valid, ambil MMSI
        mmsi = result[0]
        print(f"[INFO] Input terdeteksi sebagai ID Agen. MMSI terkait: {mmsi}")

        # Cek nama kapal berdasarkan MMSI
        cursor.execute("SELECT nama_kapal FROM data_kapal WHERE MMSI = %s", (mmsi,))
        kapal = cursor.fetchone()
        cursor.execute("SELECT zona FROM data_kapal WHERE MMSI = %s", (mmsi,))
        zona = cursor.fetchone()
        cursor.execute("SELECT jenis_kapal FROM data_kapal WHERE MMSI = %s", (mmsi,))
        jenis = cursor.fetchone()

        cursor.execute("SELECT nama FROM daftar_petugas WHERE id_petugas = %s", (id_petugas,))
        petugas = cursor.fetchone()

        if kapal:
            nama_kapal = kapal[0]
            zona_kapal = zona [0]
            jenis_kapal = jenis [0]
            nama_petugas = petugas [0]
            print(f"[INFO] Nama Petugas Penanggung Jawab: {nama_petugas}")
            log_chat("AI", f"[INFO] Nama Petugas Penanggung Jawab: {nama_petugas}")
            print(f"[INFO] MMSI valid. Nama Kapal: {nama_kapal}")
            log_chat("AI", f"[INFO] MMSI valid. Nama Kapal: {nama_kapal}")
            print(f"[INFO] MMSI valid. Zona Kapal: {zona_kapal}")
            log_chat("AI", f"[INFO] MMSI valid. Zona Kapal: {zona_kapal}")
            print(f"[INFO] MMSI valid. Jenis Kapal: {jenis_kapal}")
            log_chat("AI", f"[INFO] MMSI valid. Jenis Kapal: {jenis_kapal}")
            print(f"[INFO] Koordinat Kapal: {latitude}, {longitude}")
            log_chat("AI", f"[INFO] Koordinat Kapal: {latitude}, {longitude}")

            # Simpan ke data_monitor
            new_id = get_next_id('data_monitor')
            cursor.execute("""
                INSERT INTO data_monitor (id_monitor, id_petugas, MMSI, id_agen, latitude, longitude, pelabuhan_asal)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """, (new_id, id_petugas, mmsi, identitas, latitude, longitude, 'CIREBON'))
            db.commit()

            return nama_kapal, new_id
        else:
            print("[ERROR] MMSI tidak ditemukan di tabel data_kapal.")
            speak("Maaf capt, MMSI terkait tidak ditemukan dalam data kapal kami.")
            return None, None, None, None

    except mysql.connector.Error as err:
        print(f"[ERROR] Database: {err}")
        return None, None, None,None
    finally:
        cursor.close()
        db.close()

if len(sys.argv) >= 5:
    identitas_input = sys.argv[1]
    id_petugas_login = sys.argv[2]
    latitude = sys.argv[3]
    longitude = sys.argv[4]

    print(f"[DEBUG] Diterima dari PHP - MMSI: {identitas_input}, ID Petugas: {id_petugas_login}, Koordinat: {latitude},{longitude}")
    
    nama_kapal_ditemukan, id_monitor = input_identitas(identitas_input, id_petugas_login, latitude, longitude)
    
    if not nama_kapal_ditemukan:
        speak("Maaf capt, ID Agen tidak ditemukan dalam sistem kami. Program dihentikan.")
        sys.exit()
else:
    print("[ERROR] Argumen dari PHP tidak lengkap.")
    sys.exit()

# === Load Model Intent ===
with open("intent_classifier.pkl", "rb") as f:
    model_data = pickle.load(f)

model = model_data["model"]
vectorizer = model_data["vectorizer"]
label_encoder = model_data["label_encoder"]
intents = model_data["intents"]

# === Fungsi Membersihkan Teks ===
def clean_text(text):
    text = text.lower()
    noise = ["over", "ya", "baik", "oke", "correct", "betul", "iya", "siap"]

    # Hapus kata noise hanya jika berdiri sendiri (bukan bagian dari kata lain)
    for word in noise:
        # \b adalah boundary agar tidak menghapus bagian dari kata seperti "surabaya"
        text = re.sub(r'\b' + re.escape(word) + r'\b', '', text)

    # Hapus tanda baca tertentu
    text = re.sub(r'[.,]', '', text)
    # Hilangkan spasi berlebih akibat penghapusan kata
    text = re.sub(r'\s+', ' ', text)

    return text.strip()

# === Ekstraksi Informasi Penting ===
def extract_important_info(input_str, keyword):
    input_str = clean_text(input_str)
    keyword = keyword.lower()
    patterns = []

    if keyword == "tujuan":
        patterns = [
            r"tujuan(?: kapal)?(?: kami)?(?: adalah)?(?: ke)?\s+([a-zA-Z\s]+)",
            r"kapal\s*(?:menuju|hendak ke)\s+([a-zA-Z\s]+)",
            r"kami\s*(?:berangkat ke|menuju ke|tujuannya ke)\s+([a-zA-Z\s]+)",
            r"(?:berlayar ke|akan ke|menuju ke)\s+([a-zA-Z\s]+)",
            r"(?:destinasi|arah kapal)\s*(?:adalah)?\s+([a-zA-Z\s]+)",
            r"ke\s+([a-zA-Z\s]+)\s*(?:tujuan|tujuannya)?"
            ]
    elif keyword == "muatan":
        patterns = [
            r"(?:muatan|barang|kargo|isi kapal)(?: kami| kapal)?(?: adalah| berupa| berisi)?\s+([a-zA-Z0-9\s]{3,30})\b",
            r"kapal(?: membawa| mengangkut)\s+([a-zA-Z0-9\s]{3,30})\b",
            r"kami(?: bawa| angkut)\s+([a-zA-Z0-9\s]{3,30})\b",
            r"(?:bermuatan|dimuat)\s+([a-zA-Z0-9\s]{3,30})\b"
        ]
    elif keyword == "berat":
        patterns = [
            r"(?:berat|tonase|jumlah muatan)(?: muatan)?(?: sekitar| kira-kira)?\s+([0-9.,]+\s*\w+)",
            r"muatan(?: seberat| sebanyak)?\s+([0-9.,]+\s*\w+)",
            r"(?:membawa|mengangkut)\s+([0-9.,]+\s*\w+)",
            r"tonase\s+([0-9.,]+\s*\w+)"
        ]
    elif keyword == "draft depan":
        patterns = [
            r"(?:draft|kedalaman) depan(?: adalah| sekitar)?\s+([0-9]+)\s*meter",
            r"bagian depan(?: memiliki)?(?: draft| kedalaman)?(?: sekitar)?\s+([0-9]+)\s*meter",
            r"depan(?: kapal)?\s+([0-9]+)\s*meter"
        ]
    elif keyword == "draft belakang":
        patterns = [
            r"(?:draft|kedalaman) belakang(?: adalah| sekitar)?\s+([0-9]+)\s*meter",
            r"bagian belakang(?: memiliki)?(?: draft| kedalaman)?(?: sekitar)?\s+([0-9]+)\s*meter",
            r"belakang(?: kapal)?\s+([0-9]+)\s*meter"
        ]
    elif keyword == "nama kapten":
        patterns = [
            r"(?:nama(?: lengkap)?(?: kapten| saya)?)\s*(?:adalah|:)?\s*([a-zA-Z\s]+)",
            r"(?:saya|kapten)\s*(?:bernama|adalah)?\s*([a-zA-Z\s]+)",
            r"perkenalkan(?: saya)?\s*([a-zA-Z\s]+)"
        ]
    elif keyword == "jumlah kru":
        patterns = [
            r"(?:jumlah|total)?\s*(?:awak|kru|crew|personel)?(?: kami)?(?: adalah| berjumlah)?\s*(\d+)",
            r"terdiri dari\s*(\d+)\s*(?:orang|awak|kru|crew)",
            r"(?:ada|memiliki)\s*(\d+)\s*(?:orang|awak|kru|personel)"
        ]
    else:
        # fallback hati-hati
        patterns = [
            r"\b([0-9]+(?:[.,][0-9]+)?)\b",  # angka
            r"\b([a-zA-Z0-9\s]{3,30})\b"     # frasa pendek saja
        ]

    for pattern in patterns:
        match = re.search(pattern, input_str)
        if match:
            hasil = match.group(1).strip()
            if hasil:
                return hasil
    # Jika tidak ada pola yang cocok, beri fallback atau info debug
    if DEBUG:
        print(f"[DEBUG] Tidak ditemukan pola cocok untuk input: '{input_str}' dengan keyword.")
    return ""  # Kembalikan string kosong agar tidak salah input

def preprocess_datetime(text):
    now = datetime.now()
    text = text.lower()

    # Frasa umum
    if "sekarang" in text:
        return now
    elif "lusa depan" in text:
        return datetime(now.year, now.month, now.day + 2)
    elif "minggu depan" in text:
        return datetime(now.year, now.month, now.day + 7)
    elif "besok pagi" in text:
        return datetime(now.year, now.month, now.day + 1, 8, 0)
    elif "besok siang" in text:
        return datetime(now.year, now.month, now.day + 1, 13, 0)
    elif "besok sore" in text or "besok malam" in text:
        return datetime(now.year, now.month, now.day + 1, 19, 0)
    elif "nanti malam" in text:
        return datetime(now.year, now.month, now.day, 20, 0)
    elif "habis maghrib" in text:
        return datetime(now.year, now.month, now.day, 18, 30)
    elif "habis isya" in text:
        return datetime(now.year, now.month, now.day, 20, 0)

    # Jam tertentu, misalnya "jam 9 malam", "jam 7 pagi"
    match_jam = re.search(r"jam\s*(\d{1,2})(?:[:.](\d{1,2}))?\s*(pagi|siang|sore|malam)?", text)
    if match_jam:
        jam = int(match_jam.group(1))
        menit = int(match_jam.group(2)) if match_jam.group(2) else 0
        waktu = match_jam.group(3)

        # Konversi ke 24 jam
        if waktu == "pagi" and jam == 12:
            jam = 0
        elif waktu == "siang" and jam < 12:
            jam += 12
        elif waktu == "sore" or waktu == "malam":
            if jam < 12:
                jam += 12

        return datetime(now.year, now.month, now.day, jam, menit)

    # Tambah waktu, seperti "2 jam lagi", "3 jam ke depan"
    match_tambah_jam = re.search(r"(\d+)\s*jam\s*(lagi|ke depan)?", text)
    if match_tambah_jam:
        tambah = int(match_tambah_jam.group(1))
        return now + timedelta(hours=tambah)

    # Tambah menit, seperti "30 menit lagi"
    match_tambah_menit = re.search(r"(\d+)\s*menit\s*(lagi|ke depan)?", text)
    if match_tambah_menit:
        tambah = int(match_tambah_menit.group(1))
        return now + timedelta(minutes=tambah)

    # Tetap fallback ke dateparser jika tidak cocok
    parsed = dateparser.parse(text, languages=['id'], settings={'PREFER_DATES_FROM': 'future'})
    return parsed

def parse_datetime(date_string):
    if not date_string or not date_string.strip():
        if DEBUG:
            print("[DEBUG] Tidak ada input waktu yang diberikan.")
        return None

    manual_time = preprocess_datetime(date_string)
    if manual_time:
        if DEBUG:
            print(f"[DEBUG] Parsing manual: '{date_string}' => {manual_time}")
        return manual_time

    parsed = dateparser.parse(
        date_string, 
        languages=['id'], 
        settings={'PREFER_DATES_FROM': 'future'}
    )

    if DEBUG:
        print(f"[DEBUG] Parsing dateparser: '{date_string}' => {parsed}")

    return parsed

def format_tanggal_indonesia(dt):
    bulan_dict = {
        "January": "Januari", "February": "Februari", "March": "Maret",
        "April": "April", "May": "Mei", "June": "Juni",
        "July": "Juli", "August": "Agustus", "September": "September",
        "October": "Oktober", "November": "November", "December": "Desember"
    }
    nama_bulan = dt.strftime("%B")
    bulan_id = bulan_dict.get(nama_bulan, nama_bulan)
    return dt.strftime(f"Tanggal %d {bulan_id} %Y pukul %H:%M")

    
# === Konfirmasi Informasi ===
def confirm_info(info_text, value, keyword=""):
    while True:
        confirmation = get_voice_input(f"Kami catat {info_text} {value}, correct?")
        log_chat("AI", f"Kami catat {info_text} {value}, correct?")

        if any(word in confirmation.lower() for word in ["ya", "benar", "correct", "betul", "iya"]):
            return value
        
        # Jika salah, ulangi dan ekstrak ulang dengan regex
        ulang_input = get_voice_input(f"Kalau begitu mohon ulangi untuk {info_text}, kept?")
        log_chat("AI", f"Kalau begitu mohon ulangi untuk {info_text}, capt?")
        if keyword:
            value = extract_important_info(ulang_input, keyword)
        else:
            value = ulang_input  # fallback jika tidak ada keyword

# === Deteksi Intent ===
def respond_to_intent(user_input):
    cleaned = clean_text(user_input)
    X_input = vectorizer.transform([cleaned])
    y_pred = model.predict(X_input)
    predicted_intent = label_encoder.inverse_transform(y_pred)[0]

    for intent in intents:
        if intent["intent"] == predicted_intent:
            return random.choice(intent["responses"])
    return "Maaf capt, saya belum mengerti maksud Anda."

# === Kata kunci penting untuk disimpan ===
KATA_KUNCI_PENTING = [
    "emergency", "darurat", "kerusakan", "kebocoran", "bocor", "kebakaran", "gangguan",
    "kapal mogok", "mesin mati", "kapten sakit", "gangguan navigasi"
]

def deteksi_informasi_penting(text):
    text = clean_text(text)
    print(f"[DEBUG] Mendeteksi info penting dari: {text}")
    for kata in KATA_KUNCI_PENTING:
        if kata in text:
            print(f"[INFO] Kata penting terdeteksi: {kata}")
            return True
    return False

def simpan_informasi_penting(id_monitor, info_penting):
    db = connect_db()
    cursor = db.cursor()
    try:
        cursor.execute("""
            UPDATE data_monitor
            SET info_penting = %s
            WHERE id_monitor = %s
        """, (info_penting, id_monitor))
        db.commit()
        print("[INFO] Informasi penting disimpan ke database.")
    except mysql.connector.Error as e:
        print(f"[ERROR] Gagal menyimpan informasi penting: {e}")
    finally:
        cursor.close()
        db.close()

def update_field(id_monitor, field, value):
    db = connect_db()
    cursor = db.cursor()
    try:
        query = f"UPDATE data_monitor SET {field} = %s WHERE id_monitor = %s"
        cursor.execute(query, (value, id_monitor))
        db.commit()
        print(f"[INFO] {field} berhasil diupdate.")
    except mysql.connector.Error as e:
        print(f"[ERROR] Gagal update {field}: {e}")
    finally:
        cursor.close()
        db.close()

# === Mulai Simulasi ===
print("=== Simulasi Pelaporan Kapal ===")
speak(f"{nama_kapal_ditemukan}, V.T.S Cirebon memanggil, over.")
log_chat("AI", f"{nama_kapal_ditemukan}, VTS Cirebon memanggil, over.")

get_voice_input()

tujuan = confirm_info("tujuan ke", extract_important_info(get_voice_input("Tolong informasikan kept, tujuan kapal mau kemana? Over."), "tujuan"), keyword="tujuan")
update_field(id_monitor, "pelabuhan_tujuan", tujuan)
while True:
    berangkat_input = get_voice_input("Mohon diinformasikan waktu keberangkatan dari cirebon jam berapa, Kept? Misalnya 'besok pagi' atau 'jam 9 malam'. Over.")
    
    if not berangkat_input.strip():
        speak("Input tidak terdengar, mohon ulangi kept.")
        continue

    parsed_berangkat = parse_datetime(berangkat_input)

    if parsed_berangkat:
        formatted_berangkat = format_tanggal_indonesia(parsed_berangkat)
        confirm = get_voice_input(f"Kami catat waktu keberangkatan dari cirebon pada {formatted_berangkat}, correct?")
        log_chat("AI", f"Kami catat waktu keberangkatan dari cirebon pada {formatted_berangkat}, correct?")

        if any(word in confirm.lower() for word in ["ya", "benar", "correct", "betul", "iya"]):
            update_field(id_monitor, "waktu_keberangkatan", parsed_berangkat)
            break
        else:
            speak("Baik, mohon diulang.")
    else:
        speak("Format waktu belum sesuai kept. Coba dengan format seperti 'besok pagi', '2 jam lagi', atau 'jam 8 malam'.")

muatan = confirm_info("muatan berupa", extract_important_info(get_voice_input("Selanjutnya tolong informasinya kept, jenis muatan yang Anda bawa apa? Over."), "muatan"), keyword="muatan")
update_field(id_monitor, "jenis_muatan", muatan)
berat = confirm_info("berat muatan", extract_important_info(get_voice_input("Berapa banyak muatan kapal Anda bawa, kept? Over."), "berat"), keyword="berat")
update_field(id_monitor, "jumlah_muatan", berat)

draft_depan = confirm_info("draft depan", extract_important_info(get_voice_input("Boleh informasikan kept, untuk kedalaman draft depannya berapa meter? Over."), "draft depan"), keyword="draft depan")
try:
    draft_depan = float(draft_depan)
except ValueError:
    draft_depan = 0.0
update_field(id_monitor, "draft_depan", draft_depan)

draft_belakang = confirm_info("draft belakang", extract_important_info(get_voice_input("Boleh informasikan juga kept, untuk kedalaman draft belakangnya berapa meter? Over."), "draft belakang"), keyword="draft belakang")
try:
    draft_belakang = float(draft_belakang)
except ValueError:
    draft_belakang = 0.0
update_field(id_monitor, "draft_belakang", draft_belakang)

while True:
    eta_input = get_voice_input("Mohon diinformasikan estimasi waktu tiba di tujuannya, Kept? Misalnya 'besok pagi' atau 'jam 9 malam'. Over.")
    
    if not eta_input.strip():
        speak("Input tidak terdengar, mohon ulangi kept.")
        continue

    parsed_eta = parse_datetime(eta_input)

    if parsed_eta:
        formatted_eta = format_tanggal_indonesia(parsed_eta)
        confirm = get_voice_input(f"Kami catat estimasi tiba pada {formatted_eta}, correct?")
        log_chat("AI", f"Kami catat estimasi tiba pada {formatted_eta}, correct?")

        if any(word in confirm.lower() for word in ["ya", "benar", "correct", "betul", "iya"]):
            update_field(id_monitor, "ETA", parsed_eta)
            break
        else:
            speak("Baik, mohon diulang.")
    else:
        speak("Format waktu belum sesuai kept. Coba dengan format seperti 'besok pagi', '2 jam lagi', atau 'jam 8 malam'.")

kapten = confirm_info("nama kapten", extract_important_info(get_voice_input("Tolong informasikan kepada kami nama lengkap kapten? Over."), "nama kapten"))
update_field(id_monitor, "nama_kapten", kapten)

kru_input = extract_important_info(get_voice_input("Terakhir, berapa jumlah kru kapal Anda saat ini, kept? Over."), "jumlah kru")
kru = confirm_info("jumlah kru", kru_input)
try:
    kru = int(kru)
except ValueError:
    kru = 0  # fallback aman
update_field(id_monitor, "jumlah_kru", kru)

# === Pertanyaan Lanjutan ===
print("=== Menanyakan kebutuhan tambahan ===")
while True:
    followup = get_voice_input("Apakah ada yang bisa kami bantu lagi, kept?")
    if deteksi_informasi_penting(followup):
        print("[DEBUG] Menyimpan informasi penting:", followup)
        simpan_informasi_penting(id_monitor, followup)

    response_text = respond_to_intent(followup)

    speak(response_text)
    print("AI:", response_text)
    log_chat("AI", response_text)

    if any(word in followup.lower() for word in ["cukup", "tidak", "terima kasih", "selesai"]):
        speak("Baik, terima kasih kept. V.T.S Cirebon kembali standby channel 16, 12 over and out.")
        log_chat("AI", "Baik, terima kasih kept. VTS Cirebon kembali standby channel 16, 12 over and out.")
        print("AI: Baik, terima kasih kept. VTS Cirebon kembali standby channel 16, 12 over and out.")
        break