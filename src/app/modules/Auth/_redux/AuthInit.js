import React, { useRef, useEffect, useState } from "react";
import { shallowEqual, useSelector, connect, useDispatch } from "react-redux";
import { LayoutSplashScreen } from "../../../../_metronic/layout";
import * as auth from "./authRedux";
import { getUserByToken, login } from './authCrud';

function AuthInit(props) {
  const didRequest = useRef(false);
  const dispatch = useDispatch();
  const [showSplashScreen, setShowSplashScreen] = useState(true);
  const { authToken } = useSelector(
    ({ auth }) => ({
      authToken: auth.authToken, 
    }),

    shallowEqual
  );

  // We should request user by authToken before rendering the application
  useEffect(() => {

    
    const requestUser = async () => {
      try {
        if (!didRequest.current) {
          if (authToken !== "") {

            const user = await getUserByToken(authToken);
            dispatch(auth.actions.fulfillUser(user)); 
          }else {
            const user = await login();
            dispatch(auth.actions.fulfillUser(user));
          }
        }
      } catch (error) {
        if (!didRequest.current) {
          dispatch(auth.actions.logout());
        }
      } finally {
        setShowSplashScreen(false);
      }

      return () => (didRequest.current = true);
    };

    if (authToken) {
      requestUser(authToken);
    } else {
      dispatch(auth.actions.fulfillUser(undefined));
      setShowSplashScreen(false);
    }
    //eslint-disable-next-line
  }, []);

  return showSplashScreen ? <LayoutSplashScreen /> : <>{props.children}</>;
}

export default connect(null, auth.actions)(AuthInit);
