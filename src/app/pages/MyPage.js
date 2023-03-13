/* eslint-disable no-restricted-imports */
// React bootstrap table next =>
// DOCS: https://react-bootstrap-table.github.io/react-bootstrap-table2/docs/
// STORYBOOK: https://react-bootstrap-table.github.io/react-bootstrap-table2/storybook/index.html
import { element } from "prop-types";
import React, { useEffect, useMemo, useState } from "react";
import Table from "react-bootstrap/Table";
import { useSelector, shallowEqual, connect, useDispatch } from "react-redux";
import config from "../../config/config";

export function MyPage() {
  const user = useSelector((state) => state.auth.user);

  const [dataProduct, setDataProduct] = useState([]);
  useEffect(() => {
    GetData();
  }, []);

  function GetData() {
    const requestOptions = {
      headers: {
        Authorization:
          "271c4d716fcf0e9555b51cffed666b4321f98f7f8bbeb9ae8bfc67752b4db8a2",
      },
      method: "GET",
    };

    fetch(
      config.apiUrl + "products/GetAllProductsFromDescriptionAndReference.php",
      requestOptions
    )
      .then((response) => response.json())
      .then((result) => {
        setDataProduct(result);
      });
  }

  return (
    <>
      <Table striped bordered hover size="sm">
        <thead>
          <tr>
            <th>Name</th>
            <th>Ean</th>
            <th>Reference</th>
          </tr>
        </thead>
        {dataProduct.map((element) => {
          return (
            <>
              <tbody>
                <tr>
                  <td>{element.name}</td>
                  <td>{element.ean13}</td>
                  <td>{element.reference}</td>
                </tr>
              </tbody>
            </>
          );
        })}
      </Table>
    </>
  );
}
