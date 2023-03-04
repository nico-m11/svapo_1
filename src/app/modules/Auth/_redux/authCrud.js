import axios from "axios";
import config from "../../../../config/config";
export const LOGIN_URL = `${process.env.REACT_APP_API_URL}/auth/login`;
export const REGISTER_URL = "api/auth/register";
export const REQUEST_PASSWORD_URL = "api/auth/forgot-password";
export const ME_URL = `${process.env.REACT_APP_API_URL}/auth/me`;

export function login(email, password) {
  return axios.post(LOGIN_URL, { email, password });
}

export function register(email, fullname, username, password) {
  return axios.post(REGISTER_URL, { email, fullname, username, password });
}

export function requestPassword(email) {
  return axios.post(REQUEST_PASSWORD_URL, { email });
}


export function getUserByToken(token) {
  
  if(token !== "") {

    const requestOptions = {
      headers: {
        'Authorization': "271c4d716fcf0e9555b51cffed666b4321f98f7f8bbeb9ae8bfc67752b4db8a2",
      },
      method: 'POST',
      body: JSON.stringify({
        'accessToken' : token
      })
    };
    var user = fetch(config.apiUrl + 'users/GetUserByToken.php',
      requestOptions
    )
    .then(response=>response.json())  
    return user;
  } 
  
  
}
