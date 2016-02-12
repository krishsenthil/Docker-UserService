-- create Production user
create user 'prod-user' @'localhost' IDENTIFIED BY 'Pass@Service';
GRANT ALL PRIVILEGES ON *.* TO 'prod-user' @'localhost';
FLUSH PRIVILEGES;