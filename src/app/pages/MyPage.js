/* eslint-disable no-restricted-imports */
// React bootstrap table next =>
// DOCS: https://react-bootstrap-table.github.io/react-bootstrap-table2/docs/
// STORYBOOK: https://react-bootstrap-table.github.io/react-bootstrap-table2/storybook/index.html
import React, { useEffect, useMemo, useState } from "react";
import Table from "react-bootstrap/Table";
import { useSelector, shallowEqual, connect, useDispatch } from "react-redux";

export function MyPage() {
  const user = useSelector((state) => state.auth.user);
  const [classifica, setClassifica] = useState([]);

  const classificaFetch = async () => {
    const res = await fetch(
      `http://localhost/FantaCarnevale/api/getPrivateClassific?user=` +
        user.id_user
    );
    const data = await res.json();

    setClassifica(data);
  };

  useEffect(() => {
    classificaFetch();
  }, []);

  return (
    <>
      <Table striped>
        <thead>
          <tr>
            <th>Nome Quadriglia</th>
            <th>Nome Utente</th>
          </tr>
        </thead>

        <tbody>
          <>
            <tr>
              {classifica.map((e) => (
                <td>{e.first_team.nome_quadriglia}</td>
              ))}
              {classifica.map((e) => (
                <td>{e.first_team.nome_user}</td>
              ))}
            </tr>
            <tr>
              {/**second */}
              {classifica.map((e) => (
                <td>{e.second_team.nome_quadriglia}</td>
              ))}
              {classifica.map((e) => (
                <td>{e.second_team.nome_user}</td>
              ))}
            </tr>
            <tr>
              {/**terz */}
              {classifica.map((e) => (
                <td>{e.terz_team.nome_quadriglia}</td>
              ))}
              {classifica.map((e) => (
                <td>{e.terz_team.nome_user}</td>
              ))}
            </tr>
            <tr>
              {/** quart */}
              {classifica.map((e) => (
                <td>{e.quart_team.nome_quadriglia}</td>
              ))}
              {classifica.map((e) => (
                <td>{e.quart_team.nome_user}</td>
              ))}
            </tr>
            <tr>
              {/**quint */}
              {classifica.map((e) => (
                <td>{e.quint_team.nome_quadriglia}</td>
              ))}
              {classifica.map((e) => (
                <td>{e.quint_team.nome_user}</td>
              ))}
            </tr>
          </>
        </tbody>
      </Table>
    </>
  );
}
