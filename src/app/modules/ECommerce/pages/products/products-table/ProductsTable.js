/* eslint-disable no-restricted-imports */
// React bootstrap table next =>
// DOCS: https://react-bootstrap-table.github.io/react-bootstrap-table2/docs/
// STORYBOOK: https://react-bootstrap-table.github.io/react-bootstrap-table2/storybook/index.html
import React, { useEffect, useMemo, useState } from "react";
import Table from "react-bootstrap/Table";

export function ProductsTable() {
  const [classifica, setClassifica] = useState([]);

  const classificaFetch = async () => {
    const res = await fetch(`http://localhost/FantaCarnevale/api/bonusMalus`);
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
            <th>Quadriglia</th>
            <th>Ruolo giocatore</th>
            <th>Bonus/Malus</th>
            <th>Score</th>
          </tr>
        </thead>
        {classifica.map((e) => {
          return (
            <>
              <tbody>
                <tr>
                  <td>{e.name}</td>
                  <td>{e.role}</td>
                  <td>{e.bonus_malus}</td>
                  <td>{e.score}</td>
                </tr>
              </tbody>
            </>
          );
        })}
      </Table>
    </>
  );
}
