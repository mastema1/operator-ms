# Laravel SQL Import Guide

## Quick Start

The easiest way to import your SQL data using Laravel Artisan:

```bash
# Recommended: Import with fresh database
php artisan db:import-sql --fresh --confirm
```

## Available Commands

### 1. Custom Import Command (Recommended)
```bash
# Interactive import (asks for confirmation)
php artisan db:import-sql

# Fresh database + import (drops all tables first)
php artisan db:import-sql --fresh

# Skip confirmation prompts
php artisan db:import-sql --confirm

# Complete fresh import without prompts
php artisan db:import-sql --fresh --confirm
```

**Features:**
- ✅ Progress feedback and error reporting
- ✅ Data summary after import
- ✅ Safety confirmations
- ✅ Fresh migration option
- ✅ Proper error handling

### 2. Standard Seeder Command
```bash
# Using the custom seeder class
php artisan db:seed --class=SqlInjectionSeeder

# Force in production
php artisan db:seed --class=SqlInjectionSeeder --force
```

## What Gets Imported

When you run either command, you'll import:

- **4 Tenants** (Multi-tenant organizations)
- **5 Users** (Including test@local login with password: 12345678)
- **238 Postes** (All work positions)
- **151 Operators** (Real employee data, all on Ligne 1)
- **13 Critical Positions** (Position-based critical assignments)
- **53 Attendances** (Attendance records)
- **0 Backup Assignments** (Currently empty)

## Example Usage Scenarios

### Scenario 1: Fresh Development Setup
```bash
# Start with clean database and import all data
php artisan migrate:fresh
php artisan db:import-sql --confirm
```

### Scenario 2: Update Existing Database
```bash
# Import data into existing database (may cause conflicts)
php artisan db:seed --class=SqlInjectionSeeder
```

### Scenario 3: Production Deployment
```bash
# Fresh setup for production
php artisan migrate:fresh --force
php artisan db:import-sql --confirm --force
```

## Command Output Example

```
SQL Data Import Tool
===================
Found SQL file: 90,123 bytes
Starting data import...
Reading SQL injection file...
Executing SQL statements...
SQL injection completed!
- Statements executed: 464

Data import summary:
- Tenants: 4 records
- Users: 5 records
- Postes: 238 records
- Operators: 151 records
- Critical positions: 13 records
- Attendances: 53 records
- Backup assignments: 0 records

Data import completed successfully!
```

## Files Created

1. **`SqlInjectionSeeder.php`** - Custom seeder class
2. **`ImportSqlData.php`** - Custom Artisan command
3. **Updated `console.php`** - Command registration

## Troubleshooting

### Common Issues:

1. **File not found error**
   ```
   Solution: Ensure sql_injections.sql is in the project root
   ```

2. **Permission errors**
   ```bash
   # Run with proper database permissions
   php artisan db:import-sql --fresh --confirm
   ```

3. **Foreign key constraint errors**
   ```
   Solution: Use --fresh option to start with clean database
   ```

4. **Duplicate entry errors**
   ```
   Solution: The SQL file disables foreign key checks automatically
   ```

## Security Notes

- The imported users have hashed passwords
- test@local user password is: `12345678`
- Change passwords in production environments
- The SQL file contains sensitive data - store securely

## Next Steps After Import

1. **Login to test the system:**
   - Email: `test@local`
   - Password: `12345678`

2. **Verify data integrity:**
   ```bash
   php artisan tinker --execute="
   echo 'Tenants: ' . App\Models\Tenant::count() . PHP_EOL;
   echo 'Users: ' . App\Models\User::count() . PHP_EOL;
   echo 'Operators: ' . App\Models\Operator::count() . PHP_EOL;
   "
   ```

3. **Check the application:**
   - Visit `/dashboard` to see the imported data
   - Check `/operators` to see all imported operators
   - Verify `/postes` shows all positions

Your database is now ready with all the real operator data!
