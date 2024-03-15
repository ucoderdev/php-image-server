pm2 stop php-image-server
pm2 delete php-image-server

rm -rf pm2-errors.log
rm -rf pm2-logs.log
rm -rf pm2-output.log

pm2 start bin/run.php --name=php-image-server --log="pm2-logs.log" --error="pm2-errors.log" --output="pm2-output.log"