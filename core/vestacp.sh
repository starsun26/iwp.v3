${VESTACP_CLI}/v-add-web-domain ${VESTACP_USER} ${HOST_NAME}

GET_DO_IP=$(nslookup  ${HOST_NAME} | awk -F': ' 'NR==6 { print $2 } ')

echo 'GET_DO_IP'
echo ${GET_DO_IP}

${VESTACP_CLI}/v-change-web-domain-ip ${VESTACP_USER} ${HOST_NAME} ${GET_DO_IP}



c_read
if ${VESTACP_CLI}/v-add-letsencrypt-domain ${VESTACP_USER} ${HOST_NAME} ;then
  c_green
  echo '[OK] SSL'
# else ${VESTACP_CLI}/v-delete-web-domain ${VESTACP_USER} ${HOST_NAME};c_clean;exit
fi
c_clean

${VESTACP_CLI}/v-add-database ${VESTACP_USER} ${HOST_PREFIX} ${HOST_PREFIX} ${VESTACP_PASS}
