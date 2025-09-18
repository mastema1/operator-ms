# SQL Data Injection Instructions

## Overview
The file `sql_injections.sql` contains a complete dump of all data from your Operator Management System database. This file can be used to restore all data to another database with the same schema.

## Usage Instructions

### 1. Prerequisites
- Ensure the target database has the same schema (run all migrations first)
- Make sure you have proper database access permissions

### 2. Using MySQL Command Line
```bash
mysql -u your_username -p your_database_name < sql_injections.sql
```

### 3. Using phpMyAdmin
1. Open phpMyAdmin
2. Select your target database
3. Go to "Import" tab
4. Choose the `sql_injections.sql` file
5. Click "Go" to execute

### 4. Using Laravel Artisan (Recommended)

#### Option A: Using the Custom Seeder
```bash
php artisan db:seed --class=SqlInjectionSeeder
```

#### Option B: Using the Custom Import Command (Recommended)
```bash
# Basic import (will ask for confirmation)
php artisan db:import-sql

# Import with fresh migration (drops all tables first)
php artisan db:import-sql --fresh

# Import without confirmation prompts
php artisan db:import-sql --confirm

# Fresh migration + import without prompts
php artisan db:import-sql --fresh --confirm
```

The custom import command provides better error handling, progress feedback, and safety options.

### 5. Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your database
3. Go to File > Run SQL Script
4. Select `sql_injections.sql`
5. Execute the script

## Data Included

The SQL file contains complete data for:
- **Tenants**: 4 records (Multi-tenant organizations)
- **Users**: 5 records (Including login credentials)
- **Postes**: 238 records (Work positions)
- **Operators**: 151 records (Employee data from real_operators.csv)
- **Critical Positions**: 13 records (Critical position assignments)
- **Attendances**: 53 records (Attendance tracking data)
- **Backup Assignments**: 0 records (No backup assignments currently)

## Important Notes

1. **Foreign Key Constraints**: The script disables foreign key checks at the beginning and re-enables them at the end
2. **Data Integrity**: All relationships between tables are preserved
3. **Passwords**: User passwords are properly hashed and included
4. **Tenant Isolation**: Multi-tenant data structure is maintained
5. **Critical Positions**: Uses the new position-based critical status system

## Security Considerations

- This file contains sensitive data including hashed passwords
- Store this file securely and do not share it publicly
- Consider encrypting the file if storing long-term
- Remove or regenerate passwords in production environments

## Troubleshooting

If you encounter errors:
1. Ensure the target database is empty or you're okay with data conflicts
2. Check that all required tables exist (run migrations first)
3. Verify database user has INSERT permissions
4. Check for character encoding issues (file is UTF-8)

## File Information

- **Generated**: 2025-09-18 12:54:29
- **File Size**: ~90KB
- **Total Records**: 464 records across all tables
- **Format**: Standard MySQL INSERT statements
