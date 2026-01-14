# Event Registration API - Részletes Dokumentáció

## Projekt Áttekintés

Az **Event Registration** egy Laravel alapú backend API, amely eseményregisztrációk kezelésére szolgál. A rendszer támogatja a felhasználókezelést, eseménykezelést és regisztrációkat, Laravel Sanctum token-alapú autentikációval.

---

## Adatbázis Séma

### Users Tábla
```sql
- id (Primary Key)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- phone (string, nullable)
- is_admin (boolean, default: false)
- password (string, hashed)
- deleted_at (soft delete)
- remember_token
- created_at, updated_at
```

### Events Tábla
```sql
- id (Primary Key)
- title (string)
- description (text, nullable)
- date (datetime)
- location (string)
- max_attendees (integer)
- deleted_at (soft delete)
- created_at, updated_at
```

### Registrations Tábla
```sql
- id (Primary Key)
- user_id (Foreign Key -> users.id, cascade delete)
- event_id (Foreign Key -> events.id, cascade delete)
- status (enum: 'függőben', 'elfogadva', 'elutasítva', default: 'függőben')
- registered_at (timestamp)
- deleted_at (soft delete)
- created_at, updated_at
- UNIQUE constraint:  (user_id, event_id)
```

---

## Autentikáció

Az API Laravel Sanctum-ot használ Bearer Token alapú autentikációhoz. 

### Teszt Felhasználók

#### Admin felhasználó:
- **Email**: admin@events.hu
- **Jelszó**:  admin123
- **Jogosultságok**: Admin funkciók (CRUD műveletek mindenhol)

#### Teszt felhasználó:
- **Email**: test@events.hu
- **Jelszó**: test123
- **Jogosultságok**: Alapvető felhasználói funkciók

---

## API Végpontok

### Base URL
```
http://localhost:8000/api
```

---

## Nyilvános Végpontok (Autentikáció nélkül)

### Ping - API Állapot Ellenőrzés

**Endpoint**:  `GET /api/ping`

**Leírás**: Ellenőrzi, hogy az API működik-e.

**Postman Request**:
```http
GET http://localhost:8000/api/ping
```

**Response** (200 OK):
```json
{
  "message": "API működik"
}
```

---

### Regisztráció

**Endpoint**: `POST /api/register`

**Leírás**: Új felhasználó regisztrálása.

**Request Headers**:
```
Content-Type: application/json
Accept: application/json
```

**Request Body**:
```json
{
  "name": "Kovács János",
  "email": "kovacs. janos@example.com",
  "password":  "password123",
  "password_confirmation": "password123",
  "phone": "+36 30 123 4567"
}
```

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/register' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
  "name": "Kovács János",
  "email": "kovacs.janos@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+36 30 123 4567"
}'
```

**Response** (201 Created):
```json
{
  "message": "User created successfully",
  "user":  {
    "id": 13,
    "name": "Kovács János",
    "email": "kovacs.janos@example.com",
    "phone": "+36 30 123 4567"
  }
}
```

**Response** (422 Validation Error):
```json
{
  "message": "Failed to register user",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

---

### Bejelentkezés

**Endpoint**:  `POST /api/login`

**Leírás**: Felhasználó bejelentkeztetése és API token generálása.

**Request Headers**: 
```
Content-Type: application/json
Accept: application/json
```

**Request Body**:
```json
{
  "email": "admin@events.hu",
  "password": "admin123"
}
```

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/login' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
  "email": "admin@events.hu",
  "password": "admin123"
}'
```

**Response** (200 OK):
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@events.hu",
    "phone": null,
    "is_admin":  true
  },
  "access":  {
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "token_type": "Bearer"
  }
}
```

**Response** (401 Unauthorized):
```json
{
  "message": "Invalid email or password."
}
```

---

## Védett Végpontok (Autentikáció szükséges)

**Minden védett endpoint-hoz szükséges header**:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

### User Endpoints

#### Saját Profil Lekérése

**Endpoint**: `GET /api/me`

**Leírás**: A bejelentkezett felhasználó adatainak lekérése.

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/me' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

---

#### Saját Profil Frissítése

**Endpoint**: `PUT /api/me`

**Leírás**: A bejelentkezett felhasználó adatainak módosítása. 

**Request Body**:
```json
{
  "name": "Kovács János Frissített",
  "phone": "+36 30 999 8888"
}
```

**Postman cURL**: 
```bash
curl --location --request PUT 'http://localhost:8000/api/me' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "name": "Kovács János Frissített",
  "phone": "+36 30 999 8888"
}'
```

---

#### Kijelentkezés

**Endpoint**: `POST /api/logout`

**Leírás**:  Aktuális token törlése (kijelentkezés).

**Postman cURL**:
```bash
curl --location --request POST 'http://localhost:8000/api/logout' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

**Response** (200 OK):
```json
{
  "message": "Logged out successfully"
}
```

---

### Event Endpoints

#### Összes Esemény Listázása

**Endpoint**: `GET /api/events`

**Leírás**: Összes esemény lekérése.

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/events' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

**Response Sample**:
```json
[
  {
    "id":  1,
    "title":  "Tech Konferencia 2026",
    "description": "Innovatív technológiák bemutatása",
    "date": "2026-03-15T10:00:00. 000000Z",
    "location": "Budapest, Akadémia utca 13",
    "max_attendees": 100,
    "created_at": "2026-01-08T12:00:00.000000Z",
    "updated_at":  "2026-01-08T12:00:00.000000Z"
  }
]
```

---

#### Jövőbeli Események

**Endpoint**: `GET /api/events/upcoming`

**Leírás**: Csak a jövőbeli (még meg nem történt) események lekérése.

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/events/upcoming' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

---

#### Múltbeli Események

**Endpoint**: `GET /api/events/past`

**Leírás**: Csak a múltbeli (már megtörtént) események lekérése.

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/events/past' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

---

#### Események Szűrése

**Endpoint**:  `GET /api/events/filter`

**Leírás**: Események szűrése különböző paraméterek alapján.

**Query Paraméterek**:
- `location`: Helyszín szerinti szűrés
- `date_from`: Kezdő dátum
- `date_to`: Záró dátum
- `max_attendees_min`: Min résztvevő szám
- `max_attendees_max`: Max résztvevő szám

**Postman Request**:
```http
GET http://localhost:8000/api/events/filter?location=Budapest&date_from=2026-01-01
```

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/events/filter? location=Budapest&date_from=2026-01-01' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

---

#### Új Esemény Létrehozása (Admin Only)

**Endpoint**: `POST /api/events`

**Leírás**: Új esemény létrehozása (csak adminoknak).

**Request Body**: 
```json
{
  "title": "Laravel Meetup 2026",
  "description": "Laravel fejlesztők találkozója és tapasztalatcsere",
  "date": "2026-05-20 18:00:00",
  "location": "Budapest, Király utca 26",
  "max_attendees": 50
}
```

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/events' \
--header 'Authorization:  Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "title": "Laravel Meetup 2026",
  "description": "Laravel fejlesztők találkozója és tapasztalatcsere",
  "date": "2026-05-20 18:00:00",
  "location": "Budapest, Király utca 26",
  "max_attendees": 50
}'
```

**Response** (201 Created):
```json
{
  "id": 15,
  "title": "Laravel Meetup 2026",
  "description": "Laravel fejlesztők találkozója és tapasztalatcsere",
  "date": "2026-05-20T18:00:00.000000Z",
  "location": "Budapest, Király utca 26",
  "max_attendees": 50,
  "created_at": "2026-01-14T10:30:00.000000Z",
  "updated_at":  "2026-01-14T10:30:00.000000Z"
}
```

---

#### Esemény Módosítása (Admin Only)

**Endpoint**: `PUT /api/events/{id}`

**Leírás**: Meglévő esemény adatainak módosítása (csak adminoknak).

**Request Body**:
```json
{
  "title": "Laravel Meetup 2026 - Módosított",
  "max_attendees": 75
}
```

**Postman cURL**:
```bash
curl --location --request PUT 'http://localhost:8000/api/events/15' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "title": "Laravel Meetup 2026 - Módosított",
  "max_attendees": 75
}'
```

---

#### Esemény Törlése (Admin Only)

**Endpoint**: `DELETE /api/events/{id}`

**Leírás**: Esemény törlése (soft delete, csak adminoknak).

**Postman cURL**:
```bash
curl --location --request DELETE 'http://localhost:8000/api/events/15' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

**Response** (200 OK):
```json
{
  "message": "Event deleted successfully"
}
```

---

### Registration Endpoints

#### Regisztráció Eseményre

**Endpoint**: `POST /api/events/{event}/register`

**Leírás**: A bejelentkezett felhasználó jelentkezése egy eseményre.

**Postman cURL**:
```bash
curl --location --request POST 'http://localhost:8000/api/events/1/register' \
--header 'Authorization: Bearer 2|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

**Response** (201 Created):
```json
{
  "message": "Successfully registered for the event",
  "registration":  {
    "id": 25,
    "user_id": 2,
    "event_id":  1,
    "status":  "függőben",
    "registered_at": "2026-01-14T11:00:00.000000Z",
    "created_at": "2026-01-14T11:00:00.000000Z",
    "updated_at": "2026-01-14T11:00:00.000000Z"
  }
}
```

**Response** (422 Validation Error):
```json
{
  "message": "You are already registered for this event"
}
```

---

#### Regisztráció Törlése (Leiratkozás)

**Endpoint**: `DELETE /api/events/{event}/unregister`

**Leírás**: Felhasználó leiratkozása egy eseményről. 

**Postman cURL**:
```bash
curl --location --request DELETE 'http://localhost:8000/api/events/1/unregister' \
--header 'Authorization: Bearer 2|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

**Response** (200 OK):
```json
{
  "message": "Successfully unregistered from the event"
}
```

---

#### Felhasználó Eltávolítása Eseményről (Admin Only)

**Endpoint**: `DELETE /api/events/{event}/users/{user}`

**Leírás**: Admin törli egy felhasználó regisztrációját egy eseményről.

**Postman cURL**:
```bash
curl --location --request DELETE 'http://localhost:8000/api/events/1/users/5' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

**Response** (200 OK):
```json
{
  "message": "User removed from event successfully"
}
```

---

### User Management (Admin Only)

#### Összes Felhasználó Listázása

**Endpoint**: `GET /api/users`

**Leírás**: Összes felhasználó lekérése (csak adminoknak).

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/users' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept:  application/json'
```

---

#### Egy Felhasználó Adatai

**Endpoint**: `GET /api/users/{id}`

**Leírás**: Egy adott felhasználó adatainak lekérése (csak adminoknak).

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/users/2' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

---

#### Új Felhasználó Létrehozása (Admin)

**Endpoint**: `POST /api/users`

**Leírás**: Új felhasználó létrehozása admin által. 

**Request Body**:
```json
{
  "name":  "Nagy Péter",
  "email": "nagy. peter@events.hu",
  "password":  "password123",
  "password_confirmation": "password123",
  "phone": "+36 20 555 6677",
  "is_admin": false
}
```

**Postman cURL**:
```bash
curl --location 'http://localhost:8000/api/users' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
  "name": "Nagy Péter",
  "email": "nagy.peter@events.hu",
  "password": "password123",
  "password_confirmation": "password123",
  "phone":  "+36 20 555 6677",
  "is_admin": false
}'
```

---

#### Felhasználó Módosítása

**Endpoint**: `PUT /api/users/{id}`

**Leírás**: Felhasználó adatainak módosítása (csak adminoknak).

**Request Body**:
```json
{
  "name": "Nagy Péter Módosított",
  "is_admin": true
}
```

**Postman cURL**:
```bash
curl --location --request PUT 'http://localhost:8000/api/users/13' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "name": "Nagy Péter Módosított",
  "is_admin": true
}'
```

---

#### Felhasználó Törlése

**Endpoint**: `DELETE /api/users/{id}`

**Leírás**: Felhasználó törlése (soft delete, csak adminoknak).

**Postman cURL**:
```bash
curl --location --request DELETE 'http://localhost:8000/api/users/13' \
--header 'Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890' \
--header 'Accept: application/json'
```

**Response** (200 OK):
```json
{
  "message": "User deleted successfully"
}
```

---

## Jogosultságok Összefoglalása

| Endpoint Csoport | Nyilvános | User | Admin |
|-----------------|-----------|------|-------|
| `/ping` | ✅ | ✅ | ✅ |
| `/register` | ✅ | ✅ | ✅ |
| `/login` | ✅ | ✅ | ✅ |
| `/me` (GET, PUT) | ❌ | ✅ | ✅ |
| `/logout` | ❌ | ✅ | ✅ |
| `/events` (GET, filter, upcoming, past) | ❌ | ✅ | ✅ |
| `/events` (POST, PUT, DELETE) | ❌ | ❌ | ✅ |
| `/events/{event}/register` | ❌ | ✅ | ✅ |
| `/events/{event}/unregister` | ❌ | ✅ | ✅ |
| `/events/{event}/users/{user}` (DELETE) | ❌ | ❌ | ✅ |
| `/users` (all CRUD) | ❌ | ❌ | ✅ |

---

## Postman Collection Importálása

### Környezeti Változók Beállítása

**Postman Environment létrehozása**: 

1. Postman → Environments → Create Environment
2. Név: `Event Registration Local`
3. Változók: 

| Változó | Initial Value | Current Value |
|---------|--------------|---------------|
| `base_url` | `http://localhost:8000/api` | `http://localhost:8000/api` |
| `admin_token` | | (login után kitöltődik) |
| `user_token` | | (login után kitöltődik) |

### Pre-request Script Token Kezeléshez

Login endpoint-hoz add hozzá ezt a **Tests** script-et: 
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("admin_token", jsonData.access. token);
}
```

### Authorization Használata

Minden védett endpoint-hoz: 
1. Authorization tab → Type: Bearer Token
2. Token: `{{admin_token}}` vagy `{{user_token}}`

---

## ⚙️ Telepítés és Használat

### Környezet Beállítása

1. **Repository klónozása**:
```bash
git clone https://github.com/Mtblnt01/eventRegistration.git
cd eventRegistration
```

2. **Függőségek telepítése**:
```bash
composer install
npm install
```

3. **.env fájl beállítása**:
```bash
cp .env.example .env
php artisan key:generate
```

4. **Adatbázis konfiguráció** (. env):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_registration
DB_USERNAME=root
DB_PASSWORD=
```

5. **Migrációk és seederek futtatása**:
```bash
php artisan migrate --seed
```

6. **Szerver indítása**:
```bash
php artisan serve
```

---

## Használati Esetek

### 1. Admin belépés és esemény létrehozása
```bash
# 1. Bejelentkezés admin-ként
POST /api/login
Body:  {"email":  "admin@events.hu", "password": "admin123"}

# 2. Token mentése a válaszból

# 3. Új esemény létrehozása
POST /api/events
Header: Authorization: Bearer {token}
Body: {esemény adatok}
```

### 2. Felhasználó regisztrációja és jelentkezése eseményre
```bash
# 1. Regisztráció
POST /api/register
Body: {felhasználó adatok}

# 2. Bejelentkezés
POST /api/login
Body: {email, password}

# 3. Események böngészése
GET /api/events/upcoming
Header: Authorization: Bearer {token}

# 4. Jelentkezés eseményre
POST /api/events/1/register
Header: Authorization: Bearer {token}
```

---

## Status Értékek

### Registration Status
- `függőben` - Várakozó állapot (alapértelmezett)
- `elfogadva` - Elfogadott regisztráció
- `elutasítva` - Elutasított regisztráció

---

## Hibakezelés

### Gyakori HTTP Státusz Kódok

| Kód | Jelentés | Példa |
|-----|----------|-------|
| 200 | OK | Sikeres GET, PUT, DELETE |
| 201 | Created | Sikeres POST (új erőforrás) |
| 401 | Unauthorized | Hiányzó vagy érvénytelen token |
| 403 | Forbidden | Nincs jogosultság a művelethez |
| 404 | Not Found | Az erőforrás nem található |
| 422 | Validation Error | Validációs hiba a bemeneti adatokban |
| 500 | Server Error | Szerver oldali hiba |

---

## Megjegyzések

1. **Soft Delete**: A törlési műveletek nem végeznek fizikai törlést, csak `deleted_at` timestamp-et állítanak be
2. **Unique Constraint**: Egy felhasználó csak egyszer regisztrálhat egy adott eseményre
3. **Token Lejárat**: A Sanctum tokenek nem járnak le automatikusan (beállítható a config-ban)
4. **CORS**: Ha frontendből hívod az API-t, engedélyezd a CORS-t a Laravel config-ban

---



---

## Fejlesztő Információk

**Repository**:  [Mtblnt01/eventRegistration](https://github.com/Mtblnt01/eventRegistration)

**Backend Útvonalak**:  `routes/api.php`

**Controllerek**:
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/EventController.php`
- `app/Http/Controllers/Api/RegistrationController.php`
- `app/Http/Controllers/Api/UserController.php`

**Modellek**:
- `app/Models/User.php`
- `app/Models/Event.php`
- `app/Models/Registration.php`

---
