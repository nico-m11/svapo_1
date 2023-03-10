import React, { Suspense, lazy } from "react";
import { Redirect, Switch, Route } from "react-router-dom";
import { LayoutSplashScreen, ContentRoute } from "../_metronic/layout";
import { BuilderPage } from "./pages/BuilderPage";
import { MyPage } from "./pages/MyPage";
import { MyPage2 } from "./pages/MyPage2";
import { MyPage3 } from "./pages/MyPage3";
import { MyPage4 } from "./pages/MyPage4";
import { MyPage5 } from "./pages/MyPage5";
import { DashboardPage } from "./pages/DashboardPage";
import { Faq } from './modules/Auth/pages/Faq';
const GoogleMaterialPage = lazy(() =>
  import("./modules/GoogleMaterialExamples/GoogleMaterialPage")
);
const ReactBootstrapPage = lazy(() =>
  import("./modules/ReactBootstrapExamples/ReactBootstrapPage")
);
const ECommercePage = lazy(() =>
  import("./modules/ECommerce/pages/eCommercePage")
);
const UserProfilepage = lazy(() =>
  import("./modules/UserProfile/UserProfilePage")
);

export default function BasePage() {
  // useEffect(() => {
  //   console.log('Base page');
  // }, []) // [] - is required if you need only one call
  // https://reactjs.org/docs/hooks-reference.html#useeffect

  return (
    <Suspense fallback={<LayoutSplashScreen />}>
      <Switch>
        {
          /* Redirect from root URL to /dashboard. */
          <Redirect exact from="/" to="/dashboard" />
        }
        <ContentRoute path="/dashboard" component={DashboardPage} />
        <ContentRoute path="/product" component={MyPage} />
        <ContentRoute path="/order" component={MyPage2} />
        <ContentRoute path="/ordertosend" component={MyPage3} />
        <ContentRoute path="/adduser" component={MyPage4} />
        <ContentRoute path="/labelinggls" component={MyPage5} />
        <ContentRoute path="/faq" component={Faq} />
        <Route path="/user-profile" component={UserProfilepage} />
        <Redirect to="error/error-v1" />
      </Switch>
    </Suspense>
  );
}
