## GLOBAL VARS ================================================================
export HOST_PREFIX="$(date +%s | sha256sum | base64 | head -c 7)"
 
########################################################## vestacp
## config vestacp
export VESTACP_USER='admin'
export VESTACP_PASS="$(date +%s | sha256sum | base64 | head -c 22)"
export VESTACP_HTML_PATH="/home/admin/web/$HOST_NAME/public_html"
export VESTACP_SITES_PATH='/home/admin/web/'
export VESTACP_CLI='/usr/local/hestia/bin' # vesta
export VESTACP_ROOT='/usr/local/vesta/'
export VESTA=${VESTACP_ROOT}

########################################################## vestacp
## config wp
export wp_db_user="${VESTACP_USER}_${HOST_PREFIX}"
export wp_db_pass=${VESTACP_PASS}
