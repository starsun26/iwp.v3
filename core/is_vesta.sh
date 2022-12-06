if ! [ -d ${VESTACP_CLI} ]; then
    curl -O http://vestacp.com/pub/vst-install.sh || m_error  'http://vestacp.com/ NOT FOUND'  && m_successful 'install VESTACP.......' && bash vst-install.sh -f
fi


