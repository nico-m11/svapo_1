import React from "react";
import {useSubheader} from "../../../../_metronic/layout";

export const Faq = () => {
  const suhbeader = useSubheader();
  suhbeader.setTitle("FAQ");

  return (<>
  <h1>FAQ</h1>
  </>);
};
