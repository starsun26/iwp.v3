#!/usr/bin/env bash


this_path=$(dirname $0)

source ${this_path}/core/colors.sh
source ${this_path}/core/message.sh
source ${this_path}/core/input.sh

input 'Host name'

source ${this_path}/core/variable.sh
source ${this_path}/core/wp_fun.sh
#source ${this_path}/core/is_vesta.sh


input_geo
c_clean

############ Start install ############
source ${this_path}/core/vestacp.sh
source ${this_path}/core/is_wp_cli.sh


####################################
# CLEAN PATC
####################################
rm -rf ${VESTACP_HTML_PATH}/{*,.*,*/}


####################################
# WP INSTALL
####################################
wp_core   'download --force --locale='${GEO}''
wp_config 'create --dbname='${wp_db_user}' --dbuser='${wp_db_user}' --dbpass='${wp_db_pass}' --dbhost=localhost --force --locale='${GEO}''
wp_config 'set FS_METHOD direct'
wp_core   'install --url=https://'${HOST_NAME}'  --title='$(echo ${HOST_NAME%%.*}  | sed 's/\(.\)/\u\1/')' --admin_user='${wp_db_user}' --admin_password='${wp_db_pass}'  --admin_email=admin@'${HOST_NAME}''
wp_language ''${GEO}''


# wp --allow-root --path=${VESTACP_HTML_PATH} site empty

####################################
# WP SETTINGS Privacy Policy 
####################################
wp --allow-root --path=${VESTACP_HTML_PATH} post delete  $(wp --allow-root --path=${VESTACP_HTML_PATH} post list --post_type='post') --force
wp --allow-root --path=${VESTACP_HTML_PATH} post delete  $(wp --allow-root --path=${VESTACP_HTML_PATH} post list --post_type='page' --post_status=publish) --force
wp --allow-root --path=${VESTACP_HTML_PATH} post update  $(wp --allow-root --path=${VESTACP_HTML_PATH} post list --post_type='page' --post_status=draft --format=ids) --force --all --post_status=publish
DT="$(date -d '-22 day' +%F)"
DS="$(date +%T)"
DATE=($(echo ${DT} ${DS}))
echo ${DATE}
wp --allow-root --path=${VESTACP_HTML_PATH} post update  $(wp --allow-root --path=${VESTACP_HTML_PATH} post list --post_type='page' --post_status=publish --format=ids) --force --all --post_date=${DATE}


####################################
# x-icon
####################################
ic=${this_path}/icons
image_arr=($(ls ${ic}))
max=${#image_arr[*]}
v=$(( min + ($RANDOM*(max-min))>>15 ))
img=${ic}/${image_arr[${v}]}

ATTACHMENT_ID="$(wp --allow-root --path=${VESTACP_HTML_PATH} media import ${img} --porcelain)"
# beautystories.club



####################################
# WP SETTINGS OPTION
####################################
wp_option 'update site_icon '${ATTACHMENT_ID}''
wp_option 'update comment_moderation 1'
wp_option 'delete blogdescription'
wp_option 'set home https://'${HOST_NAME}''
wp_option 'set siteurl https://'${HOST_NAME}''
wp_rewrite 'structure '/%postname%/''
# wp_rewrite 'list'
# wp_rewrite 'flush'


####################################
# WP OPTIMIZE
####################################
wp_core 'update'
wp_core 'update-db'
wp_db   'optimize'
wp_db   'check'


####################################
# WP SETTINGS PLUGINS
####################################
# wp_plugin 'search Randomizer'
# wp_plugin 'list'
wp_plugin 'delete --all'
wp_plugin 'install post-date-randomizer --force'
cp -r ${this_path}/plugins  ${VESTACP_HTML_PATH}/wp-content/   #lmport
wp_plugin 'update --all --dry-run --exclude=keitaro-tracker-integration'
wp_plugin 'activate --all'


####################################
# WP SETTINGS MENU
####################################
en_category1=(YOGA SPORT SPORTS STRETCHING STRETCH TRAINING WORKOUT FITNESS BODIART)
en_category2=(HEALTH 'HEALTHY-NUTRITION' 'HEALTHY-FOOD' RECIPES POWER ENERGY)
en_category3=(NATURAL BEAUTY 'NATURAL-BEAUTY' 'THE-BEAUTY' APPEARANCE)

es_category1=(YOGA DEPORTE DEPORTES EXTENSIÓN TRAMO FORMACIÓN 'RUTINA-DE-EJERCICIO' APTITUD BODIART)
es_category2=(SALUD 'NUTRICIÓN-SALUDABLE' 'COMIDA-SANA' 'ALIMENTOS-SALUDABLES' 'COMIDA-SALUDABLE' RECETAS PODER ENERGÍA FUERZA FACULTAD VIGOR ÑEQUE)
es_category3=(NATURAL NORMAL BECUADRO INSTINTIVO BELLEZA BELLA MONADA 'BELLEZA-NATURAL' 'LA-BELLEZA' APARICIÓN)

min=1

#EN------------------------
max=${#en_category1[*]}
v=$(( min + ($RANDOM*(max-min))>>15 ))
en_category1=${en_category1[${v}]}


max=${#en_category2[*]}
v=$(( min + ($RANDOM*(max-min))>>15 ))
en_category2=${en_category2[${v}]}


max=${#en_category3[*]}
v=$(( min + ($RANDOM*(max-min))>>15 ))
en_category3=${en_category3[${v}]}



#ES------------------------
max=${#es_category1[*]}
v=$(( min + ($RANDOM*(max-min))>>15 ))
es_category1=${es_category1[${v}]}


max=${#es_category2[*]}
v=$(( min + ($RANDOM*(max-min))>>15 ))
es_category2=${es_category2[${v}]}


max=${#es_category3[*]}
v=$(( min + ($RANDOM*(max-min))>>15 ))
es_category3=${es_category3[${v}]}



case ${GEO} in
    'en_US') LONG_MENU=( ${en_category1} ${en_category2} ${en_category3} );about=About ;;
    'es_ES') LONG_MENU=( ${es_category1} ${es_category2} ${es_category3} );about=Mas ;;
esac


wp_menu 'create mymemu'


for i in "${!LONG_MENU[@]}"; do

    porcelain=$(wp_term 'create category '${LONG_MENU[$i]}' --porcelain')
    wp_menu 'item add-term mymemu category '${porcelain}''

done 


porcelain=$(wp_menu 'item add-custom mymemu '${about}' # --porcelain')
wp_menu 'item add-post mymemu '$(wp --allow-root --path=${VESTACP_HTML_PATH} post list --post_type='page' --post_status=publish --format=ids)' --parent-id='${porcelain}''


wp_menu 'location assign mymemu primary'
# wp_menu 'location assign mymemu mobile'
# wp_menu 'location assign mymemu expanded'



####################################
# PARSER
####################################


####################################
# FinAL
####################################
cp -r ${this_path}/htaccess/htaccess  ${VESTACP_HTML_PATH}/.htaccess  #lmport
chmod -R 777  ${VESTACP_HTML_PATH}/wp-content
# mkdir ${VESTACP_HTML_PATH}/pages
# echo "*/10 * * * * find /home/admin/tmp -type f -name 'sess_*' -mmin +10 -exec rm {} \;" >>  /var/spool/cron/crontabs/admin
# service cron restart



echo ""
echo "https://${HOST_NAME}/wp-admin"
echo --------------------
echo ${wp_db_user}
echo ${wp_db_pass}
