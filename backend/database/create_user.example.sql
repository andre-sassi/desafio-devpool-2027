CREATE USER IF NOT EXISTS 'app_user'@'localhost' IDENTIFIED BY 'TROQUE_ESTA_SENHA';
GRANT SELECT, INSERT, UPDATE, DELETE ON user_crud.* TO 'app_user'@'localhost';