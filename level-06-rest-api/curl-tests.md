# REST API – curl Test Commands

## Start server
```bash
php -S localhost:8888 api.php
```

## Health Check
```bash
curl -s http://localhost:8888/api/health | python3 -m json.tool
```

## Login and get token
```bash
curl -s -X POST http://localhost:8888/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"secret123"}' | python3 -m json.tool
```

## Set token variable (Linux/macOS)
```bash
TOKEN=$(curl -s -X POST http://localhost:8888/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"secret123"}' | python3 -c "import sys,json;print(json.load(sys.stdin)['data']['token'])")
```

## GET all books
```bash
curl -s -H "Authorization: Bearer $TOKEN" http://localhost:8888/api/books | python3 -m json.tool
```

## GET books with pagination + search
```bash
curl -s -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8888/api/books?q=php&limit=5&page=1" | python3 -m json.tool
```

## GET single book
```bash
curl -s -H "Authorization: Bearer $TOKEN" http://localhost:8888/api/books/1 | python3 -m json.tool
```

## POST – Create book
```bash
curl -s -X POST http://localhost:8888/api/books \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Laravel Up & Running","author":"Matt Stauffer","year":2023,"genre":"PHP","price":49.99,"stock":20}' \
  | python3 -m json.tool
```

## PUT – Full update
```bash
curl -s -X PUT http://localhost:8888/api/books/6 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Laravel Up & Running (3rd Ed)","author":"Matt Stauffer","year":2023,"genre":"PHP","price":54.99,"stock":25}' \
  | python3 -m json.tool
```

## PATCH – Partial update (price only)
```bash
curl -s -X PATCH http://localhost:8888/api/books/6 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"price":47.99}' | python3 -m json.tool
```

## DELETE
```bash
curl -s -X DELETE http://localhost:8888/api/books/6 \
  -H "Authorization: Bearer $TOKEN" | python3 -m json.tool
```

## Test unauthorized (no token)
```bash
curl -s http://localhost:8888/api/books
```

## Test editor (cannot create/delete)
```bash
EDITOR_TOKEN=$(curl -s -X POST http://localhost:8888/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"editor","password":"editor456"}' | python3 -c "import sys,json;print(json.load(sys.stdin)['data']['token'])")

curl -s -X POST http://localhost:8888/api/books \
  -H "Authorization: Bearer $EDITOR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","author":"Test","year":2024}' | python3 -m json.tool
```
