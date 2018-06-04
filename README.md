# wp_lincoln_tech
WordPress Lincoln Tech Site


## environment configuration:
```bash
cd /home/sites
git clone git@github.com:YOUR-USERNAME/wp_lincoln_tech
cd /home/sites/wp_lincoln_tech
git remote add upstream git@github.com:JellyfishGroup/wp_lincoln_tech
git remote set-url upstream --push no-pushing
git remote -v
chown apache:apache -R ./public_html/
```


## .ini configuration:
```bash
cd /home/sites/wp_lincoln_tech
cp install/wp_lincoln_tech.ini /etc/jellyfish/
cp install/wp_lincoln_tech_openhouse.ini /etc/jellyfish/
nano /etc/jellyfish/wp_lincoln_tech.ini
nano /etc/jellyfish/wp_lincoln_tech_openhouse.ini
# copy the correct definitions from dgel6 container for the .ini file
```


## vhost:
```bash
cd /home/sites/wp_lincoln_tech
cp install/wp_lincoln_tech.conf /home/vhosts/
cp install/wp_lincoln_tech_openhouse.conf /home/vhosts/
service httpd restart
```


## Install WP-CLI (WordPress Command Line Interface):
To test if WP-CLI is already installed, type in the following command:
```bash
wp --info
```

If the command is not found, run the following commands:
```bash
# Download the file
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
# Check to see if it is working
php wp-cli.phar --info
# Make it executable and move it
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp
#Retest the first command
wp --info
```

Commands can be found at http://wp-cli.org/commands/


## front end developer begin:
Are you a new developer to the company? If so, following the instruction guide here:
- http://wiki.jellyfish.tmp/index.php/Sass_Set_Up_on_a_Dev_Container


## task runner set-up:
```bash
cd /home/sites/wp_lincoln_tech/Front-End/task_runner/
npm install
gulp watch
```


## environment urls:
- **[dev]**     http://lincolntech.dgel6.dev.jellyfish.tmp
- **[build]**   http://lincolntech.build.us.jellyfish.net, 
                http://lincolntech-openhouse.build.us.jellyfish.net
- **[stage]**   http://lincolntech.stage.us.jellyfish.net, 
                http://lincolntech-openhouse.stage.us.jellyfish.net
- **[uat]**     https://uat.ltech.jellyfishhosting.net/
                https://oh-uat.ltech.jellyfishhosting.net/
- **[prod]**    https://info.lincolntech-usa.com `main site`
                https://www.lincolnedu-usa.com `open house`
                


## deployment processes:
- **[dev => build & stage]**  http://jenkins.jellyfish.local/job/Lincoln%20Tech/
- **[uat]**                   http://jenkins.jellyfishhosting.net/job/UAT%20-%20LincolnTech%20-%20AWS%20Codedeploy%20-%20Wordpres/
- **[production/live]**       http://jenkins.jellyfishhosting.net/job/Live%20-%20LincolnTech%20-%20AWS%20Codedeploy%20-%20Wordpres/


## administration access:
- Non-Production: `{URL}/wp-admin`
- Production: N/A
  - u: jf_developer | p: **[refer to TPM]**
  - u: jf_content | p: **[refer to TPM]**


## asset duplication:
The rcopy command will copy all files within the first path to the second path defined.
```sh
# connect to your dev container and execute the following to copy all uploads
rcopy root@dgel6.dev.jellyfish.tmp:/home/sites/wp_lincoln_tech/public_html/wp-content/uploads/* /home/sites/wp_lincoln_tech/public_html/wp-content/uploads/
```

```sh
# connect to your dev container and execute the following to copy all plugins
rcopy root@dgel6.dev.jellyfish.tmp:/home/sites/wp_lincoln_tech/public_html/wp-content/plugins/* /home/sites/wp_lincoln_tech/public_html/wp-content/plugins/
```

## known bugs:
- **403 Forbidden Error** may be related to the vhost not existing on your server
- White screen may be any of the following:
  - ``chown apache:apache`` the entire ``/home/sites/wp_lincoln_tech/public_html`` directory
  - If you are able to access ``/wp-admin``, the current theme may not be installed/activated correctly, **verify this first**
- "The uploaded file could not be moved to"... error is typically a migration/permissions issue
  - ``chown apache:apache`` the entire ``/home/sites/wp_lincoln_tech/public_html`` directory
    - *Still didn't work? See here: http://goo.gl/OtT0a9*
- "**Warning:** include(.../advanced-cache.php): failed to open stream: No such file or directory in **.../wp-settings.php** on line **84**"
  - Comment line 84 of the ``wp-settings.php``, or, cycle the plugin ``"W3 Total Cache"`` on and off until it disappears
