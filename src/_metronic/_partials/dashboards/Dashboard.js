/* eslint-disable no-restricted-imports */
import React, { useMemo } from "react";
import objectPath from "object-path";
import { useHtmlClassService } from "../../layout";
import { Card, Table, Form } from "react-bootstrap";
import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { useSelector, shallowEqual, connect, useDispatch } from "react-redux";
import * as auth from "./../../../app/modules/Auth";
import PrintProvider, { Print, NoPrint } from "react-easy-print";
import { createRef } from "react";
import html2canvas from "html2canvas";
import { func } from "prop-types";
import Button from "react-bootstrap/Button";
import Modal from "react-bootstrap/Modal";

export function Dashboard() {
  // const [loading, setloading] = useState(false);
  // const dispatch = useDispatch();
   const user = useSelector((state) => state.auth.user);
  // const CaptureImage = () => {
  //   html2canvas(document.getElementById("a")).then(function(canvas) {
  //     var a = document.createElement("a");
  //     a.href = canvas
  //       .toDataURL("image/png", 1.0)
  //       .replace("image/png", "image/octet-stream");
  //     a.download = title.nome_squadra + ".png";
  //     a.click();
  //   });
  // };

  // useEffect(() => {
  //   fetchDataTeam();
  // }, []);

  // const [teamUserMastro, setTeamUserMaestro] = useState([]);
  // const [teamUserGagliardetto, setTeamUserGagliardetto] = useState([]);
  // const [teamUserScenografia, setTeamUserScenografia] = useState([]);
  // const [teamUserMusica, setTeamUserMusica] = useState([]);
  // const [teamUserCostume, setTeamUserCostume] = useState([]);
  // const [teamUserJolly, setTeamUserJolly] = useState([]);
  // const [title, setTitle] = useState([]);

  // const fetchDataTeam = async () => {
  //   const rawData = await fetch(
  //     `http://localhost/FantaCarnevale/api/getSquadra?email=` + user.email
  //   );

  //   const data = await rawData.json();
  //   setTeamUserMaestro(data.maestro);
  //   setTeamUserGagliardetto(data.gagliardetto);
  //   setTeamUserScenografia(data.scenografia);
  //   setTeamUserMusica(data.musica);
  //   setTeamUserCostume(data.costume);
  //   setTeamUserJolly(data.jolly);
  //   setTitle(data);
  // };

  // function EditUser() {
  //   const [show, setShow] = useState(false);

  //   const handleClose = () => setShow(false);
  //   const handleShow = () => setShow(true);
  //   const [gagliardettoPost, setGagliardettoPost] = useState([]);
  //   const [maestroPost, setMaestroPost] = useState([]);
  //   const [scenografiaPost, setScenografiaPost] = useState([]);
  //   const [musicaPost, setMusicaPost] = useState([]);
  //   const [constumePost, setCostumePost] = useState([]);
  //   const [postData, setCurrentPost] = useState(null);

  //   useEffect(() => {
  //     gagliardetto().then((posts) => {
  //       setGagliardettoPost(posts.players);
  //     });

  //     maestro().then((posts) => {
  //       setMaestroPost(posts.players);
  //     });

  //     scenografia().then((posts) => {
  //       setScenografiaPost(posts.players);
  //     });

  //     musica().then((posts) => {
  //       setMusicaPost(posts.players);
  //     });

  //     costume().then((posts) => {
  //       setCostumePost(posts.players);
  //     });
  //   }, []);

  //   const gagliardetto = async () => {
  //     const res = await fetch(
  //       `http://localhost/FantaCarnevale/api/gagliardetto`
  //     );
  //     return await res.json();
  //   };

  //   const maestro = async () => {
  //     const res = await fetch(`http://localhost/FantaCarnevale/api/maestro`);
  //     return await res.json();
  //   };

  //   const scenografia = async () => {
  //     const res = await fetch(
  //       `http://localhost/FantaCarnevale/api/scenografia`
  //     );
  //     return await res.json();
  //   };

  //   const musica = async () => {
  //     const res = await fetch(`http://localhost/FantaCarnevale/api/musica`);
  //     return await res.json();
  //   };

  //   const costume = async () => {
  //     const res = await fetch(`http://localhost/FantaCarnevale/api/costume`);
  //     return await res.json();
  //   };

  //   const [selectCheck, setSelectCheck] = useState(null);
  //   const [gagliardettoValue, setGagliardettoValue] = useState(0);

  //   const handleCheck = (e) => {
  //     setSelectCheck(e.target.id);
  //     setGagliardettoValue(e.target.value);
  //   };

  //   const [selectCheckMaestro, setSelectCheckMaestro] = useState(null);
  //   const [maestroValue, setMaestroValue] = useState(0);

  //   const handleCheckMaestro = (e) => {
  //     setSelectCheckMaestro(e.target.id);
  //     setMaestroValue(e.target.value);
  //   };
  //   const [selectCheckScenografia, setSelectCheckScenografia] = useState(null);
  //   const [ScenografiaValue, setScenografiaValue] = useState(0);

  //   const handleCheckScenografia = (e) => {
  //     setSelectCheckScenografia(e.target.id);
  //     setScenografiaValue(e.target.value);
  //   };
  //   const [selectCheckMusica, setSelectCheckMusica] = useState(null);
  //   const [MusicaValue, setMusicaValue] = useState(0);

  //   const handleCheckMusica = (e) => {
  //     setSelectCheckMusica(e.target.id);
  //     setMusicaValue(e.target.value);
  //   };

  //   const [selectCheckCostume, setSelectCheckCostume] = useState(null);
  //   const [costumeValue, setCostumeValue] = useState(0);

  //   const handleCheckCostume = (e) => {
  //     setSelectCheckCostume(e.target.id);
  //     setCostumeValue(e.target.value);
  //   };

  //   const [selectCheckJolly, setSelectCheckJolly] = useState(null);
  //   const [jolly, setJolly] = useState(false);

  //   const handleCheckJolly = (e) => {
  //     setSelectCheckJolly(e.target.id);
  //   };

  //   var first_result =
  //     75 -
  //     gagliardettoValue -
  //     maestroValue -
  //     ScenografiaValue -
  //     MusicaValue -
  //     costumeValue;

  //   const onSubmit = () => {
  //     setloading(true);
  //     const formdata = new FormData();

  //     formdata.append("id_user", user.id_user);
  //     formdata.append("selectCheck", selectCheck);
  //     formdata.append("jolly", selectCheckJolly);
  //     formdata.append("selectCheckMaestro", selectCheckMaestro);
  //     formdata.append("selectCheckScenografia", selectCheckScenografia);
  //     formdata.append("selectCheckMusica", selectCheckMusica);
  //     formdata.append("selectCheckCostume", selectCheckCostume);
  //     formdata.append("value", first_result);

  //     const requestOptions = {
  //       method: "POST",
  //       redirect: "follow",
  //       body: formdata,
  //     };

  //     fetch(
  //       "http://localhost/FantaCarnevale/api/editUserQuadriglia",
  //       requestOptions
  //     )
  //       .then((response) => response.json())
  //       .then((result) => {
  //         setloading(false);
  //         //window.location.href = "/";
  //         console.log(result);
  //         //result
  //       })
  //       .catch((error) => console.log("error", error));

  //     //setSubmitting(true);
  //   };
  //   const [step1, setStep1] = useState(true);
  //   const [step2, setStep2] = useState(false);
  //   const [step3, setStep3] = useState(false);
  //   const [step4, setStep4] = useState(false);
  //   const [step5, setStep5] = useState(false);

  //   const handlingStep = (step1, step2, step3, step4, step5) => {
  //     setStep1(step1);
  //     setStep2(step2);
  //     setStep3(step3);
  //     setStep4(step4);
  //     setStep5(step5);

  //     if (step1 === true) {
  //       console.log("Step1");
  //     } else if (step2 === true) {
  //       console.log("Step2");
  //     } else if (step3 === true) {
  //       console.log("Step3");
  //     } else if (step4 === true) {
  //       console.log("Step4");
  //     } else if (step4 === true) {
  //       console.log("Step5");
  //     }
  //   };

  //   return (
  //     <>
  //       {/* <Button
  //         variant="primary"
  //         className={`btn  font-weight-bold px-9 py-4 my-3`}
  //         style={{ background: "#2f2d77", color: "#ffffff"}}
  //         onClick={handleShow}

  //       >
  //         Modifica squadra
  //       </Button> */}

  //       <Modal show={show} onHide={handleClose} animation={false}>
  //         <Modal.Header closeButton>
  //           <Modal.Title>Modifica squadra</Modal.Title>
  //         </Modal.Header>
  //         <Modal.Body>
  //           <div className="PannelStep d-flex justify-content-center mb-5">
  //             {" "}
  //             <span className={step1 ? "step active mr-1" : "step mr-1"}>
  //               Step <span className="stepNumber mr-1">1</span>
  //             </span>
  //             <span className="step-separator mr-1">&raquo;</span>
  //             <span className={step2 ? "step active mr-1" : "step mr-1"}>
  //               Step <span className="stepNumber mr-1">2</span>
  //             </span>
  //             <span className="step-separator mr-1">&raquo;</span>
  //             <span className={step3 ? "step active mr-1" : "step mr-1"}>
  //               Step <span className="stepNumber mr-1">3</span>
  //             </span>
  //             <span className="step-separator mr-1">&raquo;</span>
  //             <span className={step4 ? "step active mr-1" : "step mr-1"}>
  //               Step <span className="stepNumber mr-1">4</span>
  //             </span>
  //             <span className="step-separator mr-1">&raquo;</span>
  //             <span className={step5 ? "step active mr-1" : "step mr-1"}>
  //               Step <span className="stepNumber mr-1">5</span>
  //             </span>
  //           </div>

  //           <div>
  //             <div>
  //               {step1 ? (
  //                 <>
  //                   <h1 className="text-center">Seleziona un Gagliardetto</h1>
  //                   <h1 className="text-center">
  //                     {" "}
  //                     I tuoi putipù disponibili: {first_result}{" "}
  //                   </h1>
  //                   <h4 className="text-center" style={{ color: "red" }}>
  //                     {" "}
  //                     ⚠️ Il jolly che puoi scegliere deve essere un membro della
  //                     tua quadriglia ⚠️
  //                   </h4>
  //                   {selectCheckJolly == null ? (
  //                     <></>
  //                   ) : (
  //                     <h1 className="text-center">
  //                       {selectCheckJolly != null
  //                         ? "Hai scelto il tuo Jolly"
  //                         : ""}
  //                     </h1>
  //                   )}
  //                   {gagliardettoPost.map((e) => {
  //                     return (
  //                       <>
  //                         <Card id={e.id_players} className="mb-5 mt-5">
  //                           <div className=" d-flex  justify-content-around">
  //                             <Card.Img
  //                               variant="top"
  //                               style={{
  //                                 borderRadius: "50%",
  //                                 width: "35%",
  //                                 border: "gray solid 1px",
  //                                 marginTop: "5px",
  //                               }}
  //                               src={e.picture == null ? "" : e.picture}
  //                             />
  //                             <Card.Body>
  //                               <Card.Title>
  //                                 Nome: {e.name} <br /> Valore: {e.value}
  //                               </Card.Title>
  //                               <Form>
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   name={e.id_players}
  //                                   id={e.id_players}
  //                                   value={e.value}
  //                                   onChange={handleCheck}
  //                                   checked={e.id_players === selectCheck}
  //                                   label="Scegli elemento"
  //                                 />
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   label="Nomina come Jolly"
  //                                   id={e.id_players}
  //                                   name={e.id_players}
  //                                   value={e.id_players}
  //                                   disabled={jolly === true}
  //                                   onChange={handleCheckJolly}
  //                                   checked={
  //                                     e.id_players === selectCheckJolly &&
  //                                     e.id_players === selectCheck
  //                                   }
  //                                 />
  //                               </Form>
  //                             </Card.Body>
  //                           </div>
  //                         </Card>
  //                       </>
  //                     );
  //                   })}
  //                   <div className="d-flex justify-content-center">
  //                     <Button
  //                       onClick={(e) => handlingStep(0, 1, 0, 0)}
  //                       className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       style={{
  //                         background: "#2f2d77",
  //                         color: "#ffffff",
  //                       }}
  //                       disabled={selectCheck == null}
  //                     >
  //                       Avanti
  //                     </Button>
  //                   </div>
  //                 </>
  //               ) : (
  //                 <></>
  //               )}

  //               {step2 ? (
  //                 <>
  //                   <h1 className="text-center">Seleziona un Maestro</h1>
  //                   <h1 className="text-center">
  //                     {" "}
  //                     I tuoi putipù disponibili: {first_result}{" "}
  //                   </h1>
  //                   <h4 className="text-center" style={{ color: "red" }}>
  //                     {" "}
  //                     ⚠️ Il jolly deve essere uno dei cinque elementi scelti
  //                     della fanatsquadra ⚠️
  //                   </h4>
  //                   {selectCheckJolly == null ? (
  //                     <></>
  //                   ) : (
  //                     <h1>
  //                       {selectCheckJolly != null
  //                         ? "Hai scelto il tuo Jolly"
  //                         : ""}
  //                     </h1>
  //                   )}
  //                   {maestroPost.map((e) => {
  //                     return (
  //                       <>
  //                         <Card id={e.id_players} className="mb-5 mt-5">
  //                           <div className=" d-flex  justify-content-around">
  //                             <Card.Img
  //                               variant="top"
  //                               style={{
  //                                 borderRadius: "50%",
  //                                 width: "35%",
  //                                 border: "black solid 1px",
  //                                 marginTop: "5px",
  //                               }}
  //                               src={e.picture == null ? "" : e.picture}
  //                             />
  //                             <Card.Body>
  //                               <Card.Title>
  //                                 Nome: {e.name} <br /> Valore: {e.value}
  //                               </Card.Title>
  //                               <Form>
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   name={e.id_players}
  //                                   id={e.id_players}
  //                                   value={e.value}
  //                                   onChange={handleCheckMaestro}
  //                                   checked={
  //                                     e.id_players === selectCheckMaestro
  //                                   }
  //                                   label="Scegli elemento"
  //                                 />
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   label="Nomina come Jolly"
  //                                   id={e.id_players}
  //                                   disabled={jolly === true}
  //                                   name={e.id_players}
  //                                   value={e.id_players}
  //                                   onChange={handleCheckJolly}
  //                                   checked={
  //                                     e.id_players === selectCheckJolly &&
  //                                     e.id_players === selectCheckMaestro
  //                                   }
  //                                 />
  //                               </Form>
  //                             </Card.Body>
  //                           </div>
  //                         </Card>
  //                       </>
  //                     );
  //                   })}
  //                   {first_result < 0 ? (
  //                     <>
  //                       <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
  //                         <div className="alert-text text-center">
  //                           Hai superato i putipù disponibili:{" "}
  //                           <strong>{first_result}</strong>
  //                         </div>
  //                       </div>
  //                     </>
  //                   ) : (
  //                     <></>
  //                   )}
  //                   <div className="d-flex justify-content-center">
                    
  //                     <button
  //                       onClick={(e) => handlingStep(1, 0, 0, 0, 0, 0)}
  //                       className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       style={{ background: "#2f2d77", color: "#ffffff" }}
  //                       disabled={first_result < 0}
  //                     >
  //                       Indietro
  //                     </button>
  //                     <Button
  //                       onClick={(e) => handlingStep(0, 0, 1, 0, 0, 0)}
  //                       className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       style={{ background: "#2f2d77", color: "#ffffff" }}
  //                       disabled={selectCheckMaestro == null}
  //                     >
  //                       Avanti
  //                     </Button>
  //                   </div>
  //                 </>
  //               ) : (
  //                 <></>
  //               )}

  //               {step3 ? (
  //                 <>
  //                   <h1 className="text-center">Seleziona una Scenografia</h1>
  //                   <h1 className="text-center">
  //                     {" "}
  //                     I tuoi putipù disponibili: {first_result}{" "}
  //                   </h1>
  //                   <h4 className="text-center" style={{ color: "red" }}>
  //                     {" "}
  //                     ⚠️ Il jolly deve essere uno dei cinque elementi scelti
  //                     della fanatsquadra ⚠️
  //                   </h4>
  //                   {selectCheckJolly == null ? (
  //                     <></>
  //                   ) : (
  //                     <h1>
  //                       {selectCheckJolly != null
  //                         ? "Hai scelto il tuo Jolly"
  //                         : ""}
  //                     </h1>
  //                   )}
  //                   {scenografiaPost.map((e) => {
  //                     return (
  //                       <>
  //                         <Card id={e.id_players} className="mb-5 mt-5">
  //                           <div className=" d-flex  justify-content-around">
  //                             <Card.Img
  //                               variant="top"
  //                               style={{
  //                                 borderRadius: "50%",
  //                                 width: "35%",
  //                                 border: "black solid 1px",
  //                                 marginTop: "5px",
  //                               }}
  //                               src={e.picture == null ? "" : e.picture}
  //                             />
  //                             <Card.Body>
  //                               <Card.Title>
  //                                 Nome: {e.name} <br /> Valore: {e.value}
  //                               </Card.Title>
  //                               <Form>
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   name={e.id_players}
  //                                   id={e.id_players}
  //                                   value={e.value}
  //                                   onChange={handleCheckScenografia}
  //                                   checked={
  //                                     e.id_players === selectCheckScenografia
  //                                   }
  //                                   label="Scegli elemento"
  //                                 />
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   label="Nomina come Jolly"
  //                                   id={e.id_players}
  //                                   disabled={jolly === true}
  //                                   name={e.id_players}
  //                                   value={e.id_players}
  //                                   onChange={handleCheckJolly}
  //                                   checked={
  //                                     e.id_players === selectCheckJolly &&
  //                                     e.id_players === selectCheckScenografia
  //                                   }
  //                                 />
  //                               </Form>
  //                             </Card.Body>
  //                           </div>
  //                         </Card>
  //                       </>
  //                     );
  //                   })}
  //                   {first_result < 0 ? (
  //                     <>
  //                       <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
  //                         <div className="alert-text text-center">
  //                           Hai superato i putipù disponibili:{" "}
  //                           <strong>{first_result}</strong>
  //                         </div>
  //                       </div>
  //                     </>
  //                   ) : (
  //                     <></>
  //                   )}
  //                   <div className="d-flex justify-content-center">
                    
  //                     <button
  //                       onClick={(e) => handlingStep(0, 1, 0, 0, 0)}
  //                       className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       style={{ background: "#2f2d77", color: "#ffffff" }}
  //                     >
  //                       Indietro
  //                     </button>

  //                     <Button
  //                       onClick={(e) => handlingStep(0, 0, 0, 1, 0)}
  //                       className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       style={{ background: "#2f2d77", color: "#ffffff" }}
  //                       disabled={
  //                         selectCheckScenografia == null && first_result < 0
  //                       }
  //                     >
  //                       Avanti
  //                     </Button>
  //                   </div>
  //                 </>
  //               ) : (
  //                 <></>
  //               )}

  //               {step4 ? (
  //                 <>
  //                   <h1 className="text-center">Seleziona una Musica</h1>
  //                   <h1 className="text-center">
  //                     {" "}
  //                     I tuoi putipù disponibili: {first_result}{" "}
  //                   </h1>
  //                   <h4 className="text-center" style={{ color: "red" }}>
  //                     {" "}
  //                     ⚠️ Il jolly deve essere uno dei cinque elementi scelti
  //                     della fanatsquadra ⚠️
  //                   </h4>
  //                   {selectCheckJolly == null ? (
  //                     <></>
  //                   ) : (
  //                     <h1>
  //                       {selectCheckJolly != null
  //                         ? "Hai scelto il tuo Jolly"
  //                         : ""}
  //                     </h1>
  //                   )}
  //                   {musicaPost.map((e) => {
  //                     return (
  //                       <>
  //                         <Card id={e.id_players} className="mb-5 mt-5">
  //                           <div className=" d-flex  justify-content-around">
  //                             <Card.Img
  //                               variant="top"
  //                               style={{
  //                                 borderRadius: "50%",
  //                                 width: "35%",
  //                                 border: "black solid 1px",
  //                                 marginTop: "5px",
  //                               }}
  //                               src={e.picture == null ? "" : e.picture}
  //                             />
  //                             <Card.Body>
  //                               <Card.Title>
  //                                 Nome: {e.name} <br /> Valore: {e.value}
  //                               </Card.Title>
  //                               <Form>
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   name={e.id_players}
  //                                   id={e.id_players}
  //                                   value={e.value}
  //                                   onChange={handleCheckMusica}
  //                                   checked={e.id_players === selectCheckMusica}
  //                                   label="Scegli elemento"
  //                                 />
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   label="Nomina come Jolly"
  //                                   id={e.id_players}
  //                                   disabled={jolly === true}
  //                                   name={e.id_players}
  //                                   value={e.value}
  //                                   onChange={handleCheckJolly}
  //                                   checked={
  //                                     e.id_players === selectCheckJolly &&
  //                                     e.id_players === selectCheckMusica
  //                                   }
  //                                 />
  //                               </Form>
  //                             </Card.Body>
  //                           </div>
  //                         </Card>
  //                       </>
  //                     );
  //                   })}
  //                   {first_result < 0 ? (
  //                     <>
  //                       <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
  //                         <div className="alert-text text-center">
  //                           Hai superato i putipù disponibili:{" "}
  //                           <strong>{first_result}</strong>
  //                         </div>
  //                       </div>
  //                     </>
  //                   ) : (
  //                     <></>
  //                   )}
  //                   <div className="d-flex justify-content-center">
                      
  //                     <button
  //                       onClick={(e) => handlingStep(0, 0, 0, 1, 0)}
  //                       className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       style={{ background: "#2f2d77", color: "#ffffff" }}
  //                     >
  //                       Indietro
  //                     </button>

  //                     <Button
  //                       onClick={(e) => handlingStep(0, 0, 0, 0, 1)}
  //                       className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       style={{ background: "#2f2d77", color: "#ffffff" }}
  //                       disabled={selectCheckMusica == null || first_result < 0}
  //                     >
  //                       Avanti
  //                     </Button>
  //                   </div>
  //                 </>
  //               ) : (
  //                 <></>
  //               )}

  //               {step5 ? (
  //                 <>
  //                   <h1 className="text-center">Seleziona un Costume</h1>
  //                   <h1 className="text-center">
  //                     {" "}
  //                     I tuoi putipù disponibili: {first_result}{" "}
  //                   </h1>
  //                   <h4 className="text-center" style={{ color: "red" }}>
  //                     {" "}
  //                     ⚠️ Il jolly deve essere uno dei cinque elementi scelti
  //                     della fanatsquadra ⚠️
  //                   </h4>
  //                   {selectCheckJolly == null ? (
  //                     <></>
  //                   ) : (
  //                     <h1>
  //                       {selectCheckJolly != null
  //                         ? "Hai scelto il tuo Jolly"
  //                         : ""}
  //                     </h1>
  //                   )}
  //                   {/* begin: Terms and Conditions */}
  //                   {constumePost.map((e) => {
  //                     return (
  //                       <>
  //                         <Card id={e.id_players} className="mb-5 mt-5">
  //                           <div className=" d-flex  justify-content-around">
  //                             <Card.Img
  //                               variant="top"
  //                               style={{
  //                                 borderRadius: "50%",
  //                                 width: "35%",
  //                                 border: "black solid 1px",
  //                                 marginTop: "5px",
  //                               }}
  //                               src={e.picture == null ? "" : e.picture}
  //                             />
  //                             <Card.Body>
  //                               <Card.Title>
  //                                 Nome: {e.name} <br /> Valore: {e.value}
  //                               </Card.Title>
  //                               <Form>
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   name={e.id_players}
  //                                   id={e.id_players}
  //                                   value={e.value}
  //                                   onChange={handleCheckCostume}
  //                                   checked={
  //                                     e.id_players === selectCheckCostume
  //                                   }
  //                                   label="Scegli elemento"
  //                                 />
  //                                 <Form.Check
  //                                   type="checkbox"
  //                                   label="Nomina come Jolly"
  //                                   id={e.id_players}
  //                                   disabled={jolly === true}
  //                                   name={e.id_players}
  //                                   value={e.value}
  //                                   onChange={handleCheckJolly}
  //                                   checked={
  //                                     e.id_players === selectCheckJolly &&
  //                                     e.id_players === selectCheckCostume
  //                                   }
  //                                 />
  //                               </Form>
  //                             </Card.Body>
  //                           </div>
  //                         </Card>
  //                       </>
  //                     );
  //                   })}

  //                   {first_result < 0 ? (
  //                     <>
  //                       <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
  //                         <div className="alert-text text-center">
  //                           Hai superato i putipù disponibili:{" "}
  //                           <strong>{first_result}</strong>
  //                         </div>
  //                       </div>
  //                     </>
  //                   ) : (
  //                     <></>
  //                   )}

  //                   <div className="form-group d-flex flex-wrap flex-center">
  //                     <button
  //                       type="button"
  //                       className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //                       onClick={(e) => handlingStep(0, 0, 0, 1, 0)}
  //                       style={{ background: "#2f2d77", color: "#ffffff" }}
  //                     >
  //                       Indietro
  //                     </button>
  //                   </div>
  //                 </>
  //               ) : (
  //                 <></>
  //               )}
  //             </div>
  //           </div>
  //         </Modal.Body>
  //         <Modal.Footer>
  //           <Button variant="secondary" onClick={handleClose}>
  //             Close
  //           </Button>
  //           <Button
  //             variant="primary"
  //             className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
  //             style={{ background: "#2f2d77", color: "#ffffff" }}
  //             onClick={onSubmit}
  //             disabled= {
  //               selectCheckCostume == null ||
  //               first_result < 0 || 
  //               selectCheckJolly == null
  //             } 
  //           >
  //             Save Modifiche
  //             {loading && <span className="ml-3 spinner spinner-white"></span>}
  //           </Button>
  //         </Modal.Footer>
  //       </Modal>
  //     </>
  //   );
  // }
  // return (
  //   <>
  //     <Print>
  //       <Card className="mb-5 mt-5" id="a">
  //         <Card.Title
  //           className="mt-5 mb-5 text-center"
  //           style={{ color: "#8F8233" }}
  //         >
  //           Nome quadriglia {title.nome_squadra} <br />
  //         </Card.Title>

  //         {teamUserJolly.jolly_role === "gagliardetto" ? (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserGagliardetto.picture_player_gagliardeto}
  //               />
  //               <Card.Body>
  //                 <i
  //                   className="fas fa-star d-flex flex-row-reverse"
  //                   style={{ color: "#FBC52C" }}
  //                 ></i>{" "}
  //                 <span
  //                   style={{ color: "#FBC52C" }}
  //                   className="d-flex flex-row-reverse"
  //                 >
  //                   {" "}
  //                   Jolly{" "}
  //                 </span>
  //                 <p className="d-flex justify-content-start">
  //                   Gagliardetto scelto <br />
  //                 </p>
  //                 <h4> {teamUserGagliardetto.name_player_gagliardetto}</h4>
  //                 <p>
  //                   Valore Gagliardetto <br />
  //                 </p>
  //                 <h4>{teamUserGagliardetto.value_player_gagliardeto}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         ) : (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserGagliardetto.picture_player_gagliardeto}
  //               />
  //               <Card.Body>
  //                 <p className="d-flex justify-content-start">
  //                   Gagliardetto scelto <br />
  //                 </p>
  //                 <h4> {teamUserGagliardetto.name_player_gagliardetto}</h4>
  //                 <p>
  //                   Valore Gagliardetto <br />
  //                 </p>
  //                 <h4>{teamUserGagliardetto.value_player_gagliardeto}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         )}

  //         <hr />

  //         {teamUserJolly.jolly_role === "maestro" ? (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserMastro.picture_player_maestro}
  //               />
  //               <Card.Body>
  //                 <i
  //                   className="fas fa-star d-flex flex-row-reverse"
  //                   style={{ color: "#FBC52C" }}
  //                 ></i>{" "}
  //                 <span
  //                   style={{ color: "#FBC52C" }}
  //                   className="d-flex flex-row-reverse"
  //                 >
  //                   {" "}
  //                   Jolly{" "}
  //                 </span>
  //                 <p className="d-flex justify-content-start">Maestro scelto</p>
  //                 <h4>{teamUserMastro.name_player_maestro}</h4>
  //                 <p>Valore Maestro </p>
  //                 <h4>{teamUserMastro.value_player_maestro}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         ) : (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserMastro.picture_player_maestro}
  //               />
  //               <Card.Body>
  //                 <p className="d-flex justify-content-start">Maestro scelto</p>
  //                 <h4>{teamUserMastro.name_player_maestro}</h4>
  //                 <p>Valore Maestro </p>
  //                 <h4>{teamUserMastro.value_player_maestro}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         )}
  //         <hr />

  //         {teamUserJolly.jolly_role === "scenografia" ? (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserScenografia.picture_player_scenografia}
  //               />
  //               <Card.Body>
  //                 <i
  //                   className="fas fa-star d-flex flex-row-reverse"
  //                   style={{ color: "#FBC52C" }}
  //                 ></i>{" "}
  //                 <span
  //                   style={{ color: "#FBC52C" }}
  //                   className="d-flex flex-row-reverse"
  //                 >
  //                   {" "}
  //                   Jolly{" "}
  //                 </span>
  //                 <p className="d-flex justify-content-start">
  //                   Scenografie scelte
  //                 </p>
  //                 <h4>{teamUserScenografia.name_player_scenografia}</h4>
  //                 <p>Valore Scenografia: </p>
  //                 <h4>{teamUserScenografia.value_player_scenografia}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         ) : (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserScenografia.picture_player_scenografia}
  //               />
  //               <Card.Body>
  //                 <p className="d-flex justify-content-start">
  //                   Scenografie scelte
  //                 </p>
  //                 <h4>{teamUserScenografia.name_player_scenografia}</h4>
  //                 <p>Valore Scenografia: </p>
  //                 <h4>{teamUserScenografia.value_player_scenografia}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         )}

  //         <hr />

  //         {teamUserJolly.jolly_role === "costume" ? (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserMusica.picture_player_musica}
  //               />
  //               <Card.Body>
  //                 <i
  //                   className="fas fa-star d-flex flex-row-reverse"
  //                   style={{ color: "#FBC52C" }}
  //                 ></i>{" "}
  //                 <span
  //                   style={{ color: "#FBC52C" }}
  //                   className="d-flex flex-row-reverse"
  //                 >
  //                   {" "}
  //                   Jolly{" "}
  //                 </span>
  //                 <p className="d-flex justify-content-start">Costumi scelti</p>
  //                 <h4>{teamUserMusica.name_player_musica}</h4>
  //                 <p>Valore Costume</p>
  //                 <h4>{teamUserMusica.value_player_musica}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         ) : (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserMusica.picture_player_musica}
  //               />
  //               <Card.Body>
  //                 <p className="d-flex justify-content-start">Costumi scelti</p>
  //                 <h4>{teamUserMusica.name_player_musica}</h4>
  //                 <p>Valore Costume</p>
  //                 <h4>{teamUserMusica.value_player_musica}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         )}
  //         <hr />

  //         {teamUserJolly.jolly_role === "musica" ? (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserCostume.picture_player_costume}
  //               />
  //               <Card.Body>
  //                 <i
  //                   className="fas fa-star d-flex flex-row-reverse"
  //                   style={{ color: "#FBC52C" }}
  //                 ></i>{" "}
  //                 <span
  //                   style={{ color: "#FBC52C" }}
  //                   className="d-flex flex-row-reverse"
  //                 >
  //                   {" "}
  //                   Jolly{" "}
  //                 </span>
  //                 <p className="d-flex justify-content-start">Musiche scelte </p>
  //                 <h4>{teamUserCostume.name_player_costume}</h4>
  //                 <p>Valore Musica:</p>
  //                 <h4>{teamUserCostume.value_player_costume}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         ) : (
  //           <>
  //             <div className=" d-flex  justify-content-around">
  //               <Card.Img
  //                 variant="top"
  //                 style={{
  //                   borderRadius: "50%",
  //                   width: "25%",
  //                   border: "gray solid 1px",
  //                   marginTop: "5px",
  //                 }}
  //                 src={teamUserCostume.picture_player_costume}
  //               />
  //               <Card.Body>
  //                 <p className="d-flex justify-content-start">Musiche scelte </p>
  //                 <h4>{teamUserCostume.name_player_costume}</h4>

  //                 <p>Valore Musica:</p>
  //                 <h4>{teamUserCostume.value_player_costume}</h4>
  //               </Card.Body>
  //             </div>
  //           </>
  //         )}
  //       </Card>
  //     </Print>

  //     <div className="d-flex justify-content-around">
  //       <button
  //         onClick={CaptureImage}
  //         className={`btn  font-weight-bold px-9 py-4 my-3`}
  //         style={{ background: "#2f2d77", color: "#ffffff" }}
  //       >
  //         Fai uno Screen della tua squadra
  //       </button>

  //       <EditUser />
  //     </div>
  //   </>
  // );

console.log(user)
  return(<><h1>Helloo</h1></>)
}
