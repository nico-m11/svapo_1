/* eslint-disable no-restricted-imports */
// React bootstrap table next =>
// DOCS: https://react-bootstrap-table.github.io/react-bootstrap-table2/docs/
// STORYBOOK: https://react-bootstrap-table.github.io/react-bootstrap-table2/storybook/index.html
import React, { useEffect, useMemo, useState } from "react";
import Table from "react-bootstrap/Table";
import { useSelector, shallowEqual, connect, useDispatch } from "react-redux";

export function MyPage2() {
  const user = useSelector((state) => state.auth.user);
  const [loading, setLoading] = useState(false);
  const [classifica, setClassifica] = useState([]);

  const classificaFetch = async () => {
    setLoading(true);
    const res = await fetch(`http://localhost/FantaCarnevale/api/classifica`);
    const data = await res.json();
    setLoading(false);
    setClassifica(data);
  };
  console.log(user);
  useEffect(() => {
    classificaFetch();
  }, []);

  var count = 0;

  console.log(count);

  return (
    <>
      <Table striped>
        <div class="container">
          <div class="row">
            <div class="col col- col-sm- col-md col-lg- col-xxl-">
              <table class="table table-bordered">
                <thead>
                  <tr className="col col- col-sm- col-md col-lg- col-xxl-">
                  <th scope="col col- col-sm- col-md col-lg- col-xxl-">
                      #
                    </th>
                    <th scope="col col- col-sm- col-md col-lg- col-xxl-">
                      Nome Quadriglia
                    </th>
                    <th scope="col col- col-sm- col-md col-lg- col-xxl-">
                      Nome Utente
                    </th>
                    <th scope="col col- col-sm- col-md col-lg- col-xxl-">
                      Punti
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {classifica.map((e) => {
                    for (var i = 0; i < classifica.length; ++i) {
                      if (classifica[i] !== 0) count++;
                      return (
                        <>
                          <tr>
                            <td>{count}</td>
                            <td>{e.nome_quadriglia}</td>
                            <td>{e.name}</td>
                            <td>{e.score}</td>
                          </tr>
                        </>
                      );
                    }
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </Table>
    </>
  );
}
