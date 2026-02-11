# PHP_Laravel12_Fast_Excel
## Overview

This project demonstrates a complete Import and Export system in **Laravel 12** using the **FastExcel** package.
It allows exporting product data to Excel or CSV files and importing large datasets quickly with validation and minimal memory usage.

FastExcel is lightweight and faster than Laravel Excel for simple spreadsheet operations.

---

## Project Capabilities

* Export data to Excel and CSV
* Import Excel and CSV into database
* Handle large datasets efficiently
* Row validation during import
* Template file download
* Tailwind CSS user interface
* Pagination and error handling

---

## What is FastExcel

**Package Name:** rap2hpoutre/fast-excel

### Advantages

* Very fast performance
* Low memory consumption
* Simple syntax
* Suitable for large files
* No heavy dependencies

---

## Requirements

* PHP 8.2 or Higher
* Composer
* MySQL
* Node.js (Optional)
* Laravel 12 Compatible Environment

---

## Project Setup

### Step 1 – Create Laravel Project

```bash
composer create-project laravel/laravel laravel-fastexcel-demo
cd laravel-fastexcel-demo
```

### Step 2 – Install FastExcel

```bash
composer require rap2hpoutre/fast-excel
```

No additional configuration required. The package auto‑registers.

---

## Database Configuration

Edit `.env` file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fastexcel_db
DB_USERNAME=root
DB_PASSWORD=
```

Create database manually in MySQL:

```sql
CREATE DATABASE fastexcel_db;
```

---

## Migration – Products Table

Command:

```bash
php artisan make:migration create_products_table
```

### Columns

| Column      | Type     | Purpose                   |
| ----------- | -------- | ------------------------- |
| id          | bigint   | Primary Key               |
| name        | string   | Product Name              |
| description | text     | Optional Information      |
| price       | decimal  | Money Value               |
| stock       | integer  | Quantity                  |
| category    | string   | Product Group             |
| timestamps  | datetime | created_at and updated_at |

Run migration:

```bash
php artisan migrate
```

---

## Model – Product

Location: `app/Models/Product.php`

### Important Concepts

**$fillable**
Allows mass assignment such as `Product::create()`.

**$casts**
Automatically formats values.

Example:

* price → decimal
* stock → integer

---

## Factory and Seeder

Factories generate dummy data using Faker.

### Create Factory

```bash
php artisan make:factory ProductFactory --model=Product
```

### Create Seeder

```bash
php artisan make:seeder ProductSeeder
```

Register seeder inside `DatabaseSeeder` and run:

```bash
php artisan db:seed
```

Database now contains sample products.

---

## Controller – Core Logic

`ProductController.php` handles all operations.

### Methods Overview

| Method           | Purpose                      |
| ---------------- | ---------------------------- |
| index            | Show products                |
| export           | Full Excel export            |
| exportSimple     | Selected columns             |
| exportCsv        | CSV export                   |
| showImportForm   | Import UI                    |
| import           | Excel import with validation |
| downloadTemplate | Sample file download         |
| exportChunked    | Large data export            |
| exportFormatted  | Custom formatting            |

---

## Export Logic

### Basic Export

```php
(new FastExcel($products))->download('products.xlsx');
```

### Custom Columns

```php
function ($product) {
   return ['Name' => $product->name];
}
```

### CSV Export

Change file extension to `.csv`.

---

## Import Logic

### File Validation

```
'file' => 'required|mimes:xlsx,xls,csv|max:2048'
```

### Import Flow

1. Read file
2. Loop rows
3. Validate each row
4. Insert into database
5. Track success and failure

---

## Routes

Use grouped prefixes:

```
/products/export
/products/import
/products/template
```

---

## Views – User Interface

Tailwind CSS CDN is used.

### Layout

* Navigation bar
* Flash messages
* Content yield

### Index Page

* Product table
* Export buttons
* Pagination

### Import Page

* Upload form
* Instructions
* Template download

---

## Testing the Application

Start server:

```bash
php artisan serve
```

Visit:

```
http://127.0.0.1:8000
```

<img width="1717" height="966" alt="image" src="https://github.com/user-attachments/assets/45eabe33-fdf5-4eca-bfe1-68f7386a1b7e" />


### Test Checklist

| Action              | Expected Result     |
| ------------------- | ------------------- |
| Export Excel        | File downloads      |
| Export CSV          | CSV opens correctly |
| Import Valid File   | Rows inserted       |
| Import Invalid File | Errors displayed    |
| Pagination          | Works correctly     |
| Template Download   | File downloads      |

---

## Advanced Features

### Chunked Export

Uses `cursor()` instead of `all()` to reduce memory usage. Suitable for very large datasets.

### Formatted Export

* Uppercase text
* Currency symbols
* Totals calculation
* Conditional labels

### Queue Import

For enterprise‑level large files, use Laravel Jobs and queue workers.

---

## Common Errors and Fixes

| Error                     | Fix                       |
| ------------------------- | ------------------------- |
| Class FastExcel not found | Run composer install      |
| File not uploading        | Check php.ini upload size |
| Database error            | Verify .env credentials   |
| Memory error              | Use cursor()              |

---

## Performance Tips

* Use cursor() for large exports
* Limit selected columns
* Avoid all() on large tables
* Validate only required fields
* Use queues for imports

---

## Security Tips

* Validate file types
* Limit file size
* Use CSRF tokens
* Sanitize input data
* Restrict admin access

---

## Real‑World Use Cases

* Admin dashboards
* ERP systems
* Inventory management
* HR employee imports
* School student imports
* E‑commerce product uploads

---

## FastExcel vs Laravel Excel

| Feature      | FastExcel            | Laravel Excel        |
| ------------ | -------------------- | -------------------- |
| Speed        | Very Fast            | Moderate             |
| Memory Usage | Low                  | High                 |
| Features     | Basic                | Advanced             |
| Styling      | Limited              | Powerful             |
| Best For     | Simple import/export | Complex spreadsheets |

---

## Final Outcome

This project delivers a professional Laravel Import and Export System including:

* Excel Export
* CSV Export
* Excel Import
* Row Validation
* Template Download
* Pagination
* Tailwind UI
* Large Data Support
* Error Handling

