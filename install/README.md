![alt text](https://media.licdn.com/mpr/mpr/shrink_200_200/AAEAAQAAAAAAAAjKAAAAJDNiZWU0MTY1LTdkM2EtNGU0OC1iMzBjLTk3MWZjMzUzNGJiOQ.png "A WordPress Template Build")
***
A WordPress Template Build


## vhost install:
```bash
cd /home/sites/{repository_name}
cp install/{repository_name}.conf /home/vhosts/
service httpd restart
```

## .ini configuration:
```bash
cd /home/sites/{repository_name}
cp install/{repository_name}.ini /etc/jellyfish/
nano /etc/jellyfish/{repository_name}.ini
# copy the correct definitions from mbel6 container
service httpd restart
```
