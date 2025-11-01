## 🛠️ Toolkit to Generate DDD Module Files

This package provides custom toolkit to help you generate files based on **Domain-Driven Design (DDD)** architecture.

### 📦 Installation

To install the package, run the following command in your Laravel project:

```bash
composer require hitech/ddd-modular-toolkit
```

---

### Publish Configuration

```bash
# Publish all files
php artisan vendor:publish --provider="Hitech\DddModularToolkit\Providers\DddModularToolkitServiceProvider"

# Or publish separately
php artisan vendor:publish --tag=ddd-config
```

## ⚙️ Configuration Options

The `ddd.php` configuration file allows you to customize various aspects of the DDD Modular Toolkit. Below is a summary of the available options:

| Option | Description | Default Value |
|---|---|---|
| `blade.is_active` | Enables or disables Blade templating for modules. | `true` |
| `blade.path` | Defines the custom path for Blade views within modules. | `Blades` |
| `react.is_active` | Enables or disables React templating for modules (coming soon). | `true` |
| `react.path` | Defines the custom path for React views within modules. | `Views` |
| `middleware.auth` | Applies authentication middleware to module routes. | `false` |
| `middleware.api` | Applies API middleware to module routes. | `false` |


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
            └── Views/              # Module-specific react files
            └── Blades/             # Module-specific blade files
```

> Each folder serves a clear role in enforcing separation of concerns, maintainability, and scalability across your Laravel application.


### 1. Generate Module Files

```bash
php artisan make:module {name}:{submodule} [options]
```

**Description:** Generate DDD-style modular files in the `Modules/{name}` directory.

**Options:**

* `--A|--all` : Generate all components (default)
* `--D|--data` : Generate data-related files (should install [laravel-data](https://spatie.be/docs/laravel-data/v4/introduction))
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

## 📋 Requirements

- PHP ^8.1
- Laravel ^12.19
- Illuminate Support ^12.19
- Illuminate Console ^12.19
- Illuminate Database ^12.19

## 🤝 Contributing

Contributions are highly welcome! Please:

1. Fork this repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the [MIT License](LICENSE).

## 👨‍💻 Author

**dhank77**
- Email: d41113512@gmail.com

## 🙏 Acknowledgments

- Laravel Community
- All contributors who have helped

---

<div align="center">

**Made with ❤️ for Laravel Community**

[⭐ Star this repo](https://github.com/hitech/ddd-modular) | [🐛 Report Bug](https://github.com/hitech/ddd-modular/issues) | [💡 Request Feature](https://github.com/hitech/ddd-modular/issues)

</div>