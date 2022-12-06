m_error () {
  c_read
  echo $1
  c_clean
}

m_successful () {
  c_green
  echo $1
  c_clean
}