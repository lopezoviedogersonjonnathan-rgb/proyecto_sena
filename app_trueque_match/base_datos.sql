UPDATE mysql.user 
SET plugin = 'mysql_native_password', password = ''
WHERE User = 'root' AND Host = 'localhost';

FLUSH PRIVILEGES;