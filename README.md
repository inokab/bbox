# Transaction Management System

Két komponensből álló tranzakciókezelő rendszer:

- **`api/`** — Laravel 13 REST API: kereskedők kezelése, tranzakciók rögzítése, egyenlegnyilvántartás
- **`validator/`** — TypeScript microservice (Fastify): tranzakciók fraud-check validációja

## Telepítés és indítás

```bash
# Klónozd a repót, majd:
docker compose up --build
```

Az első indításkor a rendszer:
1. Felépíti a PHP 8.5 és Node 22 image-eket
2. Elindítja a PostgreSQL adatbázist
3. Lefuttatja a migrációkat
4. Elindítja az API-t és a validator service-t

| Szolgáltatás | URL |
|---|---|
| Laravel API | http://localhost:8000 |
| Validator | http://localhost:3000 |
| PostgreSQL | localhost:5432 |

## Leállítás

```bash
# Leállítás (adatok megmaradnak)
docker compose down

# Leállítás + adatbázis törlése
docker compose down -v
```

## Tesztek futtatása

```bash
cd api
php artisan test
```

## API dokumentáció

Swagger UI elérhető futó API mellett: **http://localhost:8000/docs**

---

## API végpontok

### Kereskedők

#### Kereskedő létrehozása

```
POST /api/merchants
```

```json
{
  "name": "Teszt Bolt Kft.",
  "email": "teszt@bolt.hu",
  "currency": "HUF"
}
```

Válasz (`201`):

```json
{
  "id": "019da054-bdf4-702d-88ba-7cfb5b6dd709",
  "name": "Teszt Bolt Kft.",
  "email": "teszt@bolt.hu",
  "balance": 0,
  "currency": "HUF",
  "created_at": "2026-04-19T10:00:00.000000Z"
}
```

#### Kereskedő lekérdezése

```
GET /api/merchants/{id}
```

---

### Tranzakciók

Minden tranzakció-létrehozó kéréshez kötelező az `Idempotency-Key` header (UUID formátum).  
Ugyanazzal a kulccsal ismételt kérés esetén a rendszer az eredeti tranzakciót adja vissza (`200`), nem hoz létre újat.

#### Befizetés (payment)

```
POST /api/merchants/{merchantId}/transactions
Idempotency-Key: <uuid>
```

```json
{
  "type": "payment",
  "amount": 10000
}
```

Válasz (`201`):

```json
{
  "id": "019da1c0-...",
  "merchant_id": "019da054-...",
  "idempotency_key": "550e8400-e29b-41d4-a716-446655440000",
  "type": "payment",
  "amount": 10000,
  "currency": "HUF",
  "status": "approved",
  "reason": null,
  "created_at": "2026-04-19T10:01:00.000000Z"
}
```

#### Visszatérítés (refund)

```
POST /api/merchants/{merchantId}/transactions
Idempotency-Key: <uuid>
```

```json
{
  "type": "refund",
  "amount": 5000
}
```

#### Tranzakciók listázása

```
GET /api/merchants/{merchantId}/transactions
```

---

## Üzleti szabályok

| Eset | Viselkedés |
|---|---|
| Payment | Növeli a kereskedő egyenlegét |
| Refund | Csökkenti az egyenleget; ha az összeg nagyobb az egyenlegnél → `422` |
| Validator elutasít | Tranzakció `rejected` státusszal mentve, egyenleg nem változik |
| Validator nem elérhető | Tranzakció `rejected` státusszal mentve (`reason: validator_unavailable`) |
| Duplikált Idempotency-Key | Eredeti tranzakció visszaadva, `200` |
| Idempotency-Key más kereskedőhöz tartozik | `409 Conflict` |
| Valuta eltérés | `422` — a tranzakció valutájának egyeznie kell a kereskedőével |

## Hibakódok

| HTTP | Eset |
|---|---|
| `404` | Kereskedő vagy tranzakció nem található |
| `409` | Idempotency-Key már más kereskedőhöz tartozik |
| `422` | Validációs hiba, elégtelen egyenleg, valuta eltérés |

## Validator service szabályai

A TypeScript microservice (`localhost:3000`) a következő esetekben utasít el tranzakciókat:

| Szabály | Reason |
|---|---|
| `amount <= 0` | `amount_must_be_positive` |
| `amount > 10 000 000` | `amount_exceeds_limit` |
| Nem támogatott valuta (nem HUF/EUR/USD) | `unsupported_currency` |

## Fejlesztői HTTP kérések

A `api/requests/` könyvtárban PhpStorm `.http` fájlok találhatók minden végponthoz.  
A `merchants.http`-ben a kereskedő létrehozásakor a `merchantId` automatikusan beállítódik a többi kéréshez.

```bash
docker compose stop validator
# küldj egy tranzakció kérést → status: rejected, reason: validator_unavailable
docker compose start validator
```
