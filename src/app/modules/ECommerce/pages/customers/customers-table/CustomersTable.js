/* eslint-disable no-restricted-imports */
// React bootstrap table next =>
// DOCS: https://react-bootstrap-table.github.io/react-bootstrap-table2/docs/
// STORYBOOK: https://react-bootstrap-table.github.io/react-bootstrap-table2/storybook/index.html
import React, { useEffect, useState } from "react";
import { useSelector, shallowEqual, connect, useDispatch } from "react-redux";
import { Button, Card, Form } from "react-bootstrap";
import Alert from "react-bootstrap/Alert";
import Table from "react-bootstrap/Table";

export function CustomersTable() {
  const user = useSelector((state) => state.auth.user);
  const [loading, setLoading] = useState(false);
  const [classifica, setClassifica] = useState([]);
  const [countClassifica, setCountClassifica] = useState(0);
  const [selectCheck, setSelectCheck] = useState(null);
  const [selectCheck2, setSelectCheck2] = useState(null);
  const [selectCheck3, setSelectCheck3] = useState(null);
  const [selectCheck4, setSelectCheck4] = useState(null);
  const [selectCheck5, setSelectCheck5] = useState(null);

  const handleCheck = (e) => {
    setSelectCheck(e.target.id);
  };

  const handleCheck2 = (e) => {
    setSelectCheck2(e.target.id);
  };

  const handleCheck3 = (e) => {
    setSelectCheck3(e.target.id);
  };

  const handleCheck4 = (e) => {
    setSelectCheck4(e.target.id);
  };

  const handleCheck5 = (e) => {
    setSelectCheck5(e.target.id);
  };

  const classificaFetch = async () => {
    setLoading(true);
    const res = await fetch(`http://localhost/FantaCarnevale/api/allUsers`);
    const data = await res.json();
    setLoading(false);
    setClassifica(data);
  };
  console.log(user);
  useEffect(() => {
    classificaFetch();
    classificaFetchCount();
  }, []);



  const classificaFetchCount = async () => {
    setLoading(true);
    const res = await fetch(`http://localhost/FantaCarnevale/api/allUsersCount`);
    const data = await res.json();
    setLoading(false)
    setCountClassifica(data);
  };

  const Contro = () => {
    setLoading(true);
    var formdata = new FormData();

    formdata.append("first_id_quadriglia", selectCheck);
    formdata.append("second_id_quadriglia", selectCheck2);
    formdata.append("terz_id_quadriglia", selectCheck3);
    formdata.append("quart_id_quadriglia", selectCheck4);
    formdata.append("quint_id_quadriglia", selectCheck5);
    formdata.append("user_id_user", user.id_user);

    const requestOptions = {
      method: "POST",
      redirect: "follow",
      body: formdata,
    };

    fetch(
      "http://localhost/FantaCarnevale/api/privateClassific",
      requestOptions
    )
      .then((response) => console.log(response))
      .then((result) => { console.log(result)
        setLoading(false);
        window.location.href = "/";
      })
      .catch((error) => console.log("error", error));
  };

  console.log(user);
  const CreateTeam = () => {
    return (
      <>
        {user.private === "0" ? (
          <>
            <button
              type="button"
              className="btn  mb-5 center"
              style={{ background: "#2f2d77", color: "#ffffff" }}
              onClick={Contro}
              disabled= {
                selectCheck === null ||
                selectCheck2 === null || 
                selectCheck3 === null ||
                selectCheck4 === null ||
                selectCheck5 === null
              }
            >
              Crea La tua Lega privata
              {loading && <span className="ml-3 spinner spinner-white"></span>}
            </button>
          </>
        ) : (
          <></>
        )}
      </>
    );
  };


  return (
    <>
      <CreateTeam />
      <Table striped>
      <div class="container">
        Utenti totali: {countClassifica}
        <div class="row">
          <div class="col col- col-sm- col-md col-lg- col-xxl-">
            <table class="table table-bordered">
              <thead>
                <tr className="col col- col-sm- col-md col-lg- col-xxl-">
                  {user.private === "0" ? (
                    <>
                      <th scope="col">Seleziona Membro1</th>
                    </>
                  ) : (
                    <></>
                  )}
                  <th scope="col col- col-sm- col-md col-lg- col-xxl-">Nome Quadriglia</th>
                  <th scope="col col- col-sm- col-md col-lg- col-xxl-">Nome Utente</th>
                </tr>
              </thead>
              <tbody>
                {classifica.map((e) => {
                  return (
                    <>
                      <tr>
                        {user.private === "0" ? (
                          <th>
                            <div class="custom-control custom-checkbox col">
                              <Form className="text-center">
                                <>
                                  <Form.Check
                                    type="checkbox"
                                    name={e.id_quadriglia}
                                    id={e.id_quadriglia}
                                    //value={e.value}
                                    onChange={handleCheck}
                                    checked={e.id_quadriglia === selectCheck}
                                    label="1 scelta"
                                  />
                                </>
                              </Form>
                              {selectCheck != null ? (
                                <>
                                  <Form className="text-center">
                                    <Form.Check
                                      type="checkbox"
                                      name={e.id_quadriglia}
                                      id={e.id_quadriglia}
                                      //value={e.value}
                                      onChange={handleCheck2}
                                      checked={e.id_quadriglia === selectCheck2}
                                      label="2 scelta"
                                    />
                                  </Form>
                                </>
                              ) : (
                                <></>
                              )}
                              {selectCheck2 != null ? (
                                <>
                                  <Form className="text-center">
                                    <Form.Check
                                      type="checkbox"
                                      name={e.id_quadriglia}
                                      id={e.id_quadriglia}
                                      //value={e.value}
                                      onChange={handleCheck3}
                                      checked={e.id_quadriglia === selectCheck3}
                                      label="3 scelta"
                                    />
                                  </Form>
                                </>
                              ) : (
                                <></>
                              )}
                              {selectCheck3 != null ? (
                                <>
                                  <Form className="text-center">
                                    <Form.Check
                                      type="checkbox"
                                      name={e.id_quadriglia}
                                      id={e.id_quadriglia}
                                      //value={e.value}
                                      onChange={handleCheck4}
                                      checked={e.id_quadriglia === selectCheck4}
                                      label="4 scelta"
                                    />
                                  </Form>
                                </>
                              ) : (
                                <></>
                              )}
                              {selectCheck4 != null ? (
                                <>
                                  <Form className="text-center">
                                    <Form.Check
                                      type="checkbox"
                                      name={e.id_quadriglia}
                                      id={e.id_quadriglia}
                                      //value={e.value}
                                      onChange={handleCheck5}
                                      checked={e.id_quadriglia === selectCheck5}
                                      label="5 scelta"
                                    />
                                  </Form>
                                </>
                              ) : (
                                <></>
                              )}
                            </div>
                          </th>
                        ) : (
                          <></>
                        )}
                        <td>{e.name_quadriglia}</td>
                        <td>{e.nome_user}</td>
                      </tr>
                    </>
                  );
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
