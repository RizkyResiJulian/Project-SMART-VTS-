import json
import pickle
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.preprocessing import LabelEncoder
from sklearn.naive_bayes import MultinomialNB
from sklearn.model_selection import train_test_split # Import untuk membagi data
from sklearn.metrics import accuracy_score # Import untuk menghitung akurasi

# Load dataset
with open("dataset_intent_kapal.json", "r", encoding="utf-8") as f:
    data = json.load(f)

texts = []
labels = []
intents_list = []

# Process each intent and its associated patterns
for item in data["intents"]:
    intent = item["intent"]
    for pattern in item["examples"]:
        texts.append(pattern.lower())
        labels.append(intent)
    intents_list.append({
        "intent": intent,
        "responses": item["responses"],
        "important": item.get("important", False)
    })

# Vectorize the text data
vectorizer = TfidfVectorizer()
X = vectorizer.fit_transform(texts)

# Encode labels (the intent labels)
label_encoder = LabelEncoder()
y = label_encoder.fit_transform(labels)

# --- Penambahan untuk Akurasi ---
# Bagi data menjadi data latih dan data uji
# Test size 0.2 berarti 20% data akan digunakan sebagai data uji
# random_state digunakan agar hasil pembagian data selalu sama setiap kali kode dijalankan
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train the Naive Bayes model menggunakan data latih
model = MultinomialNB()
model.fit(X_train, y_train)

# Prediksi pada data uji
y_pred = model.predict(X_test)

# Hitung akurasi
accuracy = accuracy_score(y_test, y_pred)
accuracy_percentage = accuracy * 100
# --- Akhir Penambahan ---

# Save the trained model, vectorizer, label encoder, and intents list into a pickle file
with open("intent_classifier.pkl", "wb") as f:
    pickle.dump({
        "model": model,
        "vectorizer": vectorizer,
        "label_encoder": label_encoder,
        "intents": intents_list
    }, f)

print("Model trained and saved successfully!")
print(f"Akurasi model pada data uji: {accuracy_percentage:.2f}%")