@echo off
echo Iniciando Apache e MySQL do XAMPP...
cd "C:\xampp"
start apache_start.bat
start mysql_start.bat

timeout /t 5 > nul

echo Abrindo o sistema no navegador...
start http://localhost/pdv_bar/
