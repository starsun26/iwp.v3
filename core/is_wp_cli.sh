is_wp_cli=$(wp cli version --allow-root && echo norm || echo govno )
if [ "${is_wp_cli}" == "govno" ]
then
  echo 'wp-cli insalled.........'
  curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  mv wp-cli.phar /usr/local/bin/wp
  wp cli version --allow-root
fi