#!/bin/bash
WP_OWNER=ltech
WP_GROUP=ltech
WP_ROOT=/home/sites/wp_lincoln_tech/public_html
WP_ROOT2=/home/sites/wp_lincoln_tech/public_html_oh
WS_GROUP=apache

# reset to safe defaults
find -L ${WP_ROOT} -exec chown ${WP_OWNER}:${WP_GROUP} {} \;
find -L ${WP_ROOT} -type d -exec chmod 755 {} \;
find -L ${WP_ROOT} -type f -exec chmod 644 {} \;

# allow wordpress to manage wp-config.php (but prevent world access)
chgrp ${WS_GROUP} ${WP_ROOT}/wp-config.php
chmod 660 ${WP_ROOT}/wp-config.php

# allow wordpress to manage wp-content
find -L ${WP_ROOT}/wp-content -exec chgrp ${WS_GROUP} {} \;
find -L ${WP_ROOT}/wp-content -type d -exec chmod 775 {} \;
find -L ${WP_ROOT}/wp-content -type f -exec chmod 664 {} \;


# reset to safe defaults
find -L ${WP_ROOT2} -exec chown ${WP_OWNER}:${WP_GROUP} {} \;
find -L ${WP_ROOT2} -type d -exec chmod 755 {} \;
find -L ${WP_ROOT2} -type f -exec chmod 644 {} \;

# allow wordpress to manage wp-config.php (but prevent world access)
chgrp ${WS_GROUP} ${WP_ROOT2}/wp-config.php
chmod 660 ${WP_ROOT2}/wp-config.php

# allow wordpress to manage wp-content
find -L ${WP_ROOT2}/wp-content -exec chgrp ${WS_GROUP} {} \;
find -L ${WP_ROOT2}/wp-content -type d -exec chmod 775 {} \;
find -L ${WP_ROOT2}/wp-content -type f -exec chmod 664 {} \;
