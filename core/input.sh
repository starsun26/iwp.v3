input () {
  while read -p "$1: $(c_green)" HOST_NAME; do
      if [ $HOST_NAME ]; then
          break;c_clean
      else echo "$(c_read) $1 should not be empty";c_clean;
      fi
  done
}

input_geo () {
  geo_list[1]="en_US"
  geo_list[2]="es_ES"


  select opt in "${geo_list[@]}"; do
      if [ -n "$opt" ]; then
          GEO=${geo_list[$REPLY]};
          break
      else echo "$(c_read)should not be empty";c_clean;
      fi
  done
  m_successful ${GEO}
}


