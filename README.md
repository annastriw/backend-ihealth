### 1. Install & Setup
```bash
git clone https://github.com/username/backend-ihealth.git
cd backend-ihealth
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

### 2. Konfigurasi Database (.env)
```bash
DB_DATABASE=db-ihealth
DB_USERNAME=root
DB_PASSWORD=

### 3. Migrasi & Seeder
```bash
php artisan migrate --seed

### 4. Jalankan Server
```bash
php artisan serve
