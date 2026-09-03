# Database setup for Render

This project uses MySQLi, so use a MySQL 8 or MariaDB 10.6 database. Render PostgreSQL is not compatible with the current application without changing its database layer.

## 1. Create the database

Create a MySQL database with a provider such as Aiven, Railway, Clever Cloud, or another managed MySQL host. Render Web Services do not provide a built-in MySQL database.

Import `database.sql` into the newly created database using the provider's SQL console or MySQL client:

```bash
mysql -h DB_HOST -P DB_PORT -u DB_USER -p DB_NAME < database.sql
```

If the provider does not allow `CREATE DATABASE` or `USE`, select the database first and run the table and seed statements from `database.sql` without those two lines.

## 2. Add Render environment variables

In the Render service, open **Environment** and add:

```text
DB_HOST=your-mysql-host
DB_PORT=3306
DB_NAME=your-database-name
DB_USER=your-database-user
DB_PASSWORD=your-database-password
BASE_URL=/
```

Do not commit real credentials. `.env.example` contains only local defaults and variable names.

## 3. Create the first administrator

After importing the schema, create an admin account in the SQL console. The application currently stores passwords as MD5 hashes, so replace the example hash with the MD5 hash of a strong password:

```sql
INSERT INTO users (name, email, password, role)
VALUES ('System Administrator', 'admin@example.com', 'REPLACE_WITH_MD5_HASH', 'admin');
```

The application will create the default system settings automatically if they are missing. Elections are created from the admin settings page.
