import axios from 'axios'

export function updateUser(fields) {
  return axios.patch('/api/user', fields)
}
