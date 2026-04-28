# Local Setup and Execution Guide

This guide explains how to run the **Worksuite PMS** application locally using WAMP and the Laravel development server.

## 1. Prerequisites
*   **WAMP Server**: Installed at `C:\wamp64`.
*   **PHP Version**: 8.1 or higher (WAMP PHP 8.2.29 is currently used).
*   **MySQL**: Running via WAMP.

## 2. Project Location
The project is located at:
`C:\wamp64\www\pms`

## 3. Database Configuration
The application is connected to the following database:
*   **Database Name**: `pms_updated`
*   **Username**: `root`
*   **Password**: *(empty)*
*   **Host**: `127.0.0.1`

If you need to re-import the database, the backup file is located at `db backup\pms_updated_backup.sql`.

## 4. How to Run the Application

To start the local server, follow these steps:

1.  Open **Command Prompt** or **PowerShell**.
2.  Navigate to the project directory:
    ```powershell
    cd C:\wamp64\www\pms
    ```
3.  Run the Laravel development server using the WAMP PHP executable:
    ```powershell
    C:\wamp64\bin\php\php8.2.29\php.exe artisan serve --host=127.0.0.1 --port=8000
    ```
    *(Note: If PHP is in your system PATH, you can simply run `php artisan serve`)*

4.  Open your browser and visit:
    **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

## 5. Maintenance Commands
If you make changes or encounter display issues, you can run these cleanup commands:
```powershell
# Clear all caches
C:\wamp64\bin\php\php8.2.29\php.exe artisan optimize:clear

# Run any new migrations
C:\wamp64\bin\php\php8.2.29\php.exe artisan migrate
```

---
**Status**: The application is currently configured and verified to work on the C: drive.
