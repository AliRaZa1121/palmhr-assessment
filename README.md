# Symfony REST API – Mobile Device Catalogue

A RESTful API for managing and searching mobile handsets, using Symfony, MySQL, Redis, and Docker.

---

## 🚀 Setup & Installation

1. **Clone the Repository**

   ```bash
   git clone git@github.com:AliRaZa1121/palmhr-assessment.git
   cd palmhr-assessment
   ```

2. **Copy Environment File**

   ```bash
   cp .env.dev .env
   ```

3. **Build and Start Docker Containers**

   ```bash
   docker-compose up --build -d
   ```

4. **Create Databases**
   *(In the MySQL shell, run these commands:)*

   ```bash
   docker-compose exec db mysql -u root -p
   ```

   Then in the MySQL prompt:

   ```sql
   CREATE DATABASE IF NOT EXISTS mobile_catalogue CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE DATABASE IF NOT EXISTS mobile_catalogue_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;
   ```

5. **Run Database Migrations**

   ```bash
   docker-compose exec api php bin/console doctrine:migrations:migrate --no-interaction
   docker-compose exec api php bin/console doctrine:migrations:migrate --env=test --no-interaction
   ```

6. **Load Fixtures (Optional, Recommended)**

   ```bash
   docker-compose exec api php bin/console doctrine:fixtures:load --no-interaction
   docker-compose exec api php bin/console doctrine:fixtures:load --env=test --no-interaction
   ```

---

## 🧑‍💻 Running Tests

```bash
docker-compose exec api vendor/bin/phpunit
```

---

## 🌐 Access the API

* Base URL: [http://localhost:8080/](http://localhost:8080/)
* Documentation: [http://localhost:8080/api/documentation](http://localhost:8080/api/documentation)

* Main endpoints:

  * `/api/v1/handsets`
  * `/api/v2/handsets`

---

**That’s it – you’re ready to develop and test!**
