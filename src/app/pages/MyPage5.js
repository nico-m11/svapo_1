/* eslint-disable no-restricted-imports */
// React bootstrap table next =>
// DOCS: https://react-bootstrap-table.github.io/react-bootstrap-table2/docs/
// STORYBOOK: https://react-bootstrap-table.github.io/react-bootstrap-table2/storybook/index.html
import React, { useEffect, useMemo, useState } from "react";
import Table from "react-bootstrap/Table";
import { useSelector, shallowEqual, connect, useDispatch } from "react-redux";

export function MyPage5() {
  const user = useSelector((state) => state.auth.user);

  return (
    <>
      <h1>Hello My page 5 </h1>
    </>
  );
}
