// database/migrations/2024_01_01_000001_create_users_table.php
// (Keep as is - no changes)

brew services stop mysql && \
mysqld_safe --skip-grant-tables --skip-networking & sleep 5 && \
mysql -u root -e "FLUSH PRIVILEGES; ALTER USER 'root'@'localhost' IDENTIFIED BY 'NewPassword123!';" && \
mysqladmin -u root -Maalu@123! shutdown && \
brew services start mysql

brew services stop mysql
mysqld_safe --skip-grant-tables --skip-networking &

---
mysql -u root -e "
FLUSH PRIVILEGES;
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'Admin@123';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;"

-- Create Database
CREATE DATABASE numero_db;

-- Create User
CREATE USER 'numero_user'@'localhost' IDENTIFIED BY 'NumeroPass@123';

-- Grant Permissions
GRANT ALL PRIVILEGES ON my_app_db.* TO 'myuser'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;