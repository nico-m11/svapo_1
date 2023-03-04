import React from "react"; 
import CookieConsent, { getCookieConsentValue } from "react-cookie-consent";

// il componente per funzionare in tutte le pagine è stato messo in App.js 
export const AcceptedCookies = () => {
  return(
    <>
       <CookieConsent
       onAccept={(e) => { // fuonzione per settare i cookie a true 
        if (e === false){ // se uguale a false 
          return getCookieConsentValue(e); // setto a true 
        } else {
         return e; //sempre false 
        }
       }}
        debug={false} // attivare in caso si vuole vedere sempre la barra cookie per effettuare modifiche
        expires={365} // giorni per la validità del cookie 
        location="bottom"
        buttonText="Ho Capito!"
        cookieName="CookiesCrurated"
        style={{ background: "rgb(231,232,241)", color: 'black' }}
        buttonStyle={{background: '#3399FF', color: "white", fontSize: "13px" }}
      >
        Questo sito utilizza i Cookie 🍪  <span 
        style={{ fontSize: "10px" }}>
          Read:  <a  
          style={{ color: "rgb(112,54,53)" }} 
          //href="https://crurated.com/cookie-policy/" 
          target="_blank" >
            link
            </a> 
          </span>
      </CookieConsent>
    </>
  );
}