## 🛠️ Artisan Commands to Generate DDD Module Files

This package provides custom Artisan commands to help you generate files based on **Domain-Driven Design (DDD)** architecture.

### 📦 Installation

To install the package, run the following command in your Laravel project:

```bash
composer require hitech/ddd-modular-toolkit
```

---

## 📁 Module Folder Structure (DDD Style)

The `php artisan make:modulefiles {ModuleName}` command will generate a modular structure based on **Domain-Driven Design (DDD)** principles. Here's how the generated folder structure looks:

```
app/
└── Modules/
    └── {ModuleName}/
        ├── Application/
        │   ├── Data/               # Laravel Data classes for validation/transformation
        │   ├── DTO/                # Data Transfer Objects (immutable value objects)
        │   └── Services/           # Business logic layer
        ├── Domain/
        │   └── Contracts/          # Interfaces for repositories or domain services
        │   └── Entities/           # Domain entities (business objects)
        │   └── Constants/          # Domain constants
        │   └── Enums/              # Domain enums
        ├── Infrastructure/
        │   ├── Database/
        │   │   ├── Migrations/     # Module-specific database migrations
        │   │   ├── Models/         # Eloquent models
        │   │   └── Seeders/        # Seeders for initial data
        │   └── Repositories/       # Repository implementations
        └── Interface/
            ├── Controllers/        # HTTP controllers (extends base Laravel Controller)
            ├── Requests/           # Form Request classes with validation + toDTO()
            ├── Resources/          # API resource formatters
            └── Routes/             # Module-specific route definitions
```

> Each folder serves a clear role in enforcing separation of concerns, maintainability, and scalability across your Laravel application.


### 1. Generate Module Files

```bash
php artisan make:module {name}:{submodule} [options]
```

**Description:** Generate DDD-style modular files in the `Modules/{name}` directory.

**Options:**

* `--A|--all` : Generate all components (default)
* `--D|--data` : Generate data-related files
* `--Y|--dto` : Generate DTO (Data Transfer Object)
* `--M|--migration` : Generate migration file
* `--O|--model` : Generate model
* `--R|--repository` : Generate repository
* `--S|--service` : Generate service
* `--C|--controller` : Generate controller
* `--Q|--request` : Generate request
* `--E|--resource` : Generate API resource
* `--T|--route` : Generate route file
* `--X|--seeder` : Generate database seeder

**Usage examples:**

```bash
# Generate all components for Product module
php artisan make:module Product --all

# Generate all components for Category submodule inside Product module
php artisan make:module Product:Category --all

# Generate only controller and service for Order module
php artisan make:module Order --controller --service

# Generate only controller and service for Cart submodule inside Order module
php artisan make:module Order:Cart --controller --service

# Generate repository and model for User module
php artisan make:module User -R -O

# Generate repository and model for Role submodule inside User module
php artisan make:module User:Role -R -O
```

---

### 2. Generate Modify Migration

```bash
php artisan modify:migration {module} {table} [options]
```

**Description:** Create a new migration file for modifying an existing table within a specific module.

**Options:**

* `--add-column=` or `--ac=` : Add new column
* `--rename-column=` or `--rc=` : Rename existing column
* `--drop-column=` or `--dc=` : Drop column
* `--modify-column=` or `--mc=` : Modify existing column

**Usage examples:**

```bash
# Add 'status' column to 'contracts' table in Contract module
php artisan modify:migration Contract contracts --ac=status:string

# Rename 'name' column to 'full_name' in users table
php artisan modify:migration User users --rc=name:full_name

# Drop 'temp_field' column from products table
php artisan modify:migration Product products --dc=temp_field

# Combine operations: add 'notes' column and drop 'old_field' column
php artisan modify:migration Order orders --ac=notes:text --dc=old_field
```

---
### github.com/dhank77
## HITECH AKSARA DIGITAL