# Testowanie API - Cichy Challenge

## 🚀 Uruchomienie serwera

Serwer Symfony powinien działać na porcie **8000**. Jeśli nie działa, uruchom:

```bash
symfony serve
# lub
php -S localhost:8000 -t public
```

## 📋 Endpointy API

### 1. Lista wyzwań

**GET** `/api/challenges`

Zwraca listę wszystkich wyzwań w formacie JSON.

**Status:** 200 OK

**Przykład:**

```bash
curl http://127.0.0.1:8000/api/challenges
```

### 2. Szczegóły wyzwania

**GET** `/api/challenges/{id}`

Zwraca szczegóły konkretnego wyzwania.

**Status:**

-   200 OK - jeśli wyzwanie istnieje
-   404 Not Found - jeśli wyzwanie nie istnieje
-   400 Bad Request - jeśli ID jest nieprawidłowe (np. "abc")

**Przykłady:**

```bash
# Prawidłowe ID (200 OK)
curl http://127.0.0.1:8000/api/challenges/1

# Nieistniejące wyzwanie (404 Not Found)
curl http://127.0.0.1:8000/api/challenges/999

# Nieprawidłowy ID (400 Bad Request)
curl http://127.0.0.1:8000/api/challenges/abc
```

## 🧪 Testowanie wszystkich scenariuszy

### Test 1: Lista wyzwań (200 OK)

```bash
curl -w "\nHTTP Status: %{http_code}\n" http://127.0.0.1:8000/api/challenges
```

### Test 2: Szczegóły wyzwania (200 OK)

```bash
curl -w "\nHTTP Status: %{http_code}\n" http://127.0.0.1:8000/api/challenges/1
```

### Test 3: Nieistniejące wyzwanie (404 Not Found)

```bash
curl -w "\nHTTP Status: %{http_code}\n" http://127.0.0.1:8000/api/challenges/999
```

### Test 4: Nieprawidłowy ID (400 Bad Request)

```bash
curl -w "\nHTTP Status: %{http_code}\n" http://127.0.0.1:8000/api/challenges/abc
```

## 🌐 Testowanie w przeglądarce

Możesz również testować endpointy bezpośrednio w przeglądarce:

-   **Lista wyzwań:** http://127.0.0.1:8000/api/challenges
-   **Szczegóły wyzwania:** http://127.0.0.1:8000/api/challenges/1
-   **404 Not Found:** http://127.0.0.1:8000/api/challenges/999
-   **400 Bad Request:** http://127.0.0.1:8000/api/challenges/abc

## 📊 Przykładowe odpowiedzi

### 200 OK - Lista wyzwań

```json
[
    {
        "id": 1,
        "title": "24 godziny bez social mediów",
        "description": "Wyzwanie polegające na całkowitym odcięciu się od mediów społecznościowych na 24 godziny.",
        "duration": 1,
        "difficulty": "easy",
        "isActive": true,
        "createdAt": "2025-11-22T00:13:21+00:00"
    }
]
```

### 200 OK - Szczegóły wyzwania

```json
{
    "id": 1,
    "title": "24 godziny bez social mediów",
    "description": "Wyzwanie polegające na całkowitym odcięciu się od mediów społecznościowych na 24 godziny.",
    "duration": 1,
    "difficulty": "easy",
    "isActive": true,
    "createdAt": "2025-11-22T00:13:21+00:00"
}
```

### 404 Not Found

```json
{
    "error": "Not Found",
    "message": "Challenge not found."
}
```

### 400 Bad Request

```json
{
    "error": "Bad Request",
    "message": "Invalid challenge ID. ID must be a positive integer."
}
```

## 🔧 Dodawanie przykładowych danych

Jeśli chcesz dodać więcej przykładowych wyzwań:

```bash
php bin/console app:add-sample-challenges
```

## 📝 Uwagi

-   Wszystkie odpowiedzi są w formacie JSON
-   Wszystkie endpointy zwracają odpowiednie statusy HTTP
-   Walidacja parametrów działa poprawnie
-   Błędy są zwracane w czytelnym formacie JSON
