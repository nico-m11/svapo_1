/* eslint-disable react/jsx-no-undef */

/* eslint-disable no-restricted-imports */

import React, { useEffect, useState } from "react";
import { useFormik } from "formik";
import { connect } from "react-redux";
import * as Yup from "yup";
import { Link } from "react-router-dom";
import { FormattedMessage, injectIntl } from "react-intl";
import * as auth from "../_redux/authRedux";
import { register } from "../_redux/authCrud";
import { Button, Card, Form } from "react-bootstrap";
import Alert from "react-bootstrap/Alert";
import { check } from "prettier";
import { useRef } from "react";
import { set } from "lodash";
const initialValues = {
  // primo step
  fullname: "",
  email: "",
  username: "",
  number_phone: "",
  password: "",
  changepassword: "",

  //secondo step
  teamName: "",
  acceptTerms: false,
};

function Registration(props) {
  const [gagliardettoPost, setGagliardettoPost] = useState([]);
  const [maestroPost, setMaestroPost] = useState([]);
  const [scenografiaPost, setScenografiaPost] = useState([]);
  const [musicaPost, setMusicaPost] = useState([]);
  const [constumePost, setCostumePost] = useState([]);
  const [postData, setCurrentPost] = useState(null);

  useEffect(() => {
    gagliardetto().then((posts) => {
      setGagliardettoPost(posts.players);
    });

    maestro().then((posts) => {
      setMaestroPost(posts.players);
    });

    scenografia().then((posts) => {
      setScenografiaPost(posts.players);
    });

    musica().then((posts) => {
      setMusicaPost(posts.players);
    });

    costume().then((posts) => {
      setCostumePost(posts.players);
    });
  }, []);

  const gagliardetto = async () => {
    const res = await fetch(`http://localhost/FantaCarnevale/api/gagliardetto`);
    return await res.json();
  };

  const maestro = async () => {
    const res = await fetch(`http://localhost/FantaCarnevale/api/maestro`);
    return await res.json();
  };

  const scenografia = async () => {
    const res = await fetch(`http://localhost/FantaCarnevale/api/scenografia`);
    return await res.json();
  };

  const musica = async () => {
    const res = await fetch(`http://localhost/FantaCarnevale/api/musica`);
    return await res.json();
  };

  const costume = async () => {
    const res = await fetch(`http://localhost/FantaCarnevale/api/costume`);
    return await res.json();
  };

  const [selectCheck, setSelectCheck] = useState(null);
  const [gagliardettoValue, setGagliardettoValue] = useState(0);

  const handleCheck = (e) => {
    setSelectCheck(e.target.id);
    setGagliardettoValue(e.target.value);
  };

  const [selectCheckMaestro, setSelectCheckMaestro] = useState(null);
  const [maestroValue, setMaestroValue] = useState(0);

  const handleCheckMaestro = (e) => {
    setSelectCheckMaestro(e.target.id);
    setMaestroValue(e.target.value);
  };
  const [selectCheckScenografia, setSelectCheckScenografia] = useState(null);
  const [ScenografiaValue, setScenografiaValue] = useState(0);

  const handleCheckScenografia = (e) => {
    setSelectCheckScenografia(e.target.id);
    setScenografiaValue(e.target.value);
  };
  const [selectCheckMusica, setSelectCheckMusica] = useState(null);
  const [MusicaValue, setMusicaValue] = useState(0);

  const handleCheckMusica = (e) => {
    setSelectCheckMusica(e.target.id);
    setMusicaValue(e.target.value);
  };

  const [selectCheckCostume, setSelectCheckCostume] = useState(null);
  const [costumeValue, setCostumeValue] = useState(0);

  const handleCheckCostume = (e) => {
    setSelectCheckCostume(e.target.id);
    setCostumeValue(e.target.value);
  };

  const [selectCheckJolly, setSelectCheckJolly] = useState(null);
  const [jolly, setJolly] = useState(false);

  const handleCheckJolly = (e) => {
    setSelectCheckJolly(e.target.id);
  };

  var first_result =
    75 -
    gagliardettoValue -
    maestroValue -
    ScenografiaValue -
    MusicaValue -
    costumeValue;

  const { intl } = props;
  const [loading, setLoading] = useState(false);
  const RegistrationSchema = Yup.object().shape({
    fullname: Yup.string()
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required(
        intl.formatMessage({
          id: "AUTH.VALIDATION.REQUIRED_FIELD",
        })
      ),
    email: Yup.string()
      .email("Wrong email format")
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required(
        intl.formatMessage({
          id: "AUTH.VALIDATION.REQUIRED_FIELD",
        })
      ),
    username: Yup.string()
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required(
        intl.formatMessage({
          id: "AUTH.VALIDATION.REQUIRED_FIELD",
        })
      ),
    number_phone: Yup.number()
      .min(3, "Minimum 3 symbols")
      //.max(50, "Maximum 50 symbols")
      .required(
        intl.formatMessage({
          id: "AUTH.VALIDATION.REQUIRED_FIELD",
        })
      ),
    teamName: Yup.string()
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required(
        intl.formatMessage({
          id: "AUTH.VALIDATION.REQUIRED_FIELD",
        })
      ),
    password: Yup.string()
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required(
        intl.formatMessage({
          id: "AUTH.VALIDATION.REQUIRED_FIELD",
        })
      ),
    changepassword: Yup.string()
      .required(
        intl.formatMessage({
          id: "AUTH.VALIDATION.REQUIRED_FIELD",
        })
      )
      .when("password", {
        is: (val) => (val && val.length > 0 ? true : false),
        then: Yup.string().oneOf(
          [Yup.ref("password")],
          "Password and Confirm Password didn't match"
        ),
      }),
    acceptTerms: Yup.bool().required(
      "You must accept the terms and conditions"
    ),
  });

  const enableLoading = () => {
    setLoading(true);
  };

  const disableLoading = () => {
    setLoading(false);
  };

  const getInputClasses = (fieldname) => {
    if (formik.touched[fieldname] && formik.errors[fieldname]) {
      return "is-invalid";
    }

    if (formik.touched[fieldname] && !formik.errors[fieldname]) {
      return "is-valid";
    }

    return "";
  };

  const [step1, setStep1] = useState(true);
  const [step2, setStep2] = useState(false);
  const [step3, setStep3] = useState(false);
  const [step4, setStep4] = useState(false);
  const [step5, setStep5] = useState(false);
  const [step6, setStep6] = useState(false);
  const [step7, setStep7] = useState(false);

  const handlingStep = (step1, step2, step3, step4, step5, step6, step7) => {
    setStep1(step1);
    setStep2(step2);
    setStep3(step3);
    setStep4(step4);
    setStep5(step5);
    setStep6(step6);
    setStep7(step7);

    if (step1 === true) {
      console.log("Step1");
    } else if (step2 === true) {
      console.log("Step2");
    } else if (step3 === true) {
      console.log("Step3");
    } else if (step4 === true) {
      console.log("Step4");
    } else if (step5 === true) {
      console.log("Step5");
    } else if (step6 === true) {
      console.log("Step6");
    } else if (step7 === true) {
      console.log("Step7");
    }
  };

  const formik = useFormik({
    initialValues,
    validationSchema: RegistrationSchema,
    onSubmit: (values, { setStatus, setSubmitting }) => {
      const formdata = new FormData();

      formdata.append("full_name", values.fullname);
      formdata.append("email", values.email);
      formdata.append("username", values.username);
      formdata.append("number_phone", values.number_phone);
      formdata.append("teamName", values.teamName);
      formdata.append("password", values.password);
      formdata.append("changepassword", values.changepassword);
      formdata.append("selectCheck", selectCheck);
      formdata.append("jolly", selectCheckJolly);
      formdata.append("selectCheckMaestro", selectCheckMaestro);
      formdata.append("selectCheckScenografia", selectCheckScenografia);
      formdata.append("selectCheckMusica", selectCheckMusica);
      formdata.append("selectCheckCostume", selectCheckCostume);
      formdata.append("value", first_result);

      formdata.append("acceptTerms", values.acceptTerms);

      const requestOptions = {
        method: "POST",
        redirect: "follow",
        body: formdata,
      };

      fetch("http://localhost/FantaCarnevale/api/registration", requestOptions)
        .then((response) => response.json())
        .then((result) => {
          if (result === false) {
           alert('Questa E-mail già esiste');
          }
          console.log(result);
          //result
        })
        .catch((error) => console.log("error", error));

      setSubmitting(true);
      enableLoading();
      register(
        values.email,
        values.fullname,
        values.username,
        values.password,
        values.teamName
      )
        .then(({ data: { authToken } }) => {
          props.register(authToken);
          disableLoading();
          setSubmitting(false);
          window.location.href = "/";
        })
        .catch(() => {
          setSubmitting(false);
          setStatus(
            intl.formatMessage({
              id: "AUTH.VALIDATION.INVALID_LOGIN",
            })
          );
          disableLoading();
        });
    },
  });

  return (
    <div className="login-form login-signin" style={{ display: "block" }}>
      <div className="text-center mb-10 mb-lg-20">
        <h3 className="font-size-h1">
          <FormattedMessage id="AUTH.REGISTER.TITLE" />
        </h3>
        <p className="text-muted font-weight-bold">
          Enter your details to create your account
        </p>
      </div>

      <form
        id="kt_login_signin_form"
        className="form fv-plugins-bootstrap fv-plugins-framework animated animate__animated animate__backInUp"
        onSubmit={formik.handleSubmit}
      >
        {/* begin: Alert */}
        {formik.status && (
          <div className="mb-10 alert alert-custom alert-light-danger alert-dismissible">
            <div className="alert-text font-weight-bold">{formik.status}</div>
          </div>
        )}
        {/* end: Alert */}

        <div className="PannelStep d-flex justify-content-center mb-5">
          {" "}
          <span className={step1 ? "step active mr-1" : "step mr-1"}>
            Step <span className="stepNumber mr-1">1</span>
          </span>
          <span className="step-separator mr-1">&raquo;</span>
          <span className={step2 ? "step active mr-1" : "step mr-1"}>
            Step <span className="stepNumber mr-1">2</span>
          </span>
          <span className="step-separator mr-1">&raquo;</span>
          <span className={step3 ? "step active mr-1" : "step mr-1"}>
            Step <span className="stepNumber mr-1">3</span>
          </span>
          <span className="step-separator mr-1">&raquo;</span>
          <span className={step4 ? "step active mr-1" : "step mr-1"}>
            Step <span className="stepNumber mr-1">4</span>
          </span>
          <span className="step-separator mr-1">&raquo;</span>
          <span className={step5 ? "step active mr-1" : "step mr-1"}>
            Step <span className="stepNumber mr-1">5</span>
          </span>
          <span className="step-separator mr-1">&raquo;</span>
          <span className={step6 ? "step active mr-1" : "step mr-1"}>
            Step <span className="stepNumber mr-1">6</span>
          </span>
          <span className="step-separator mr-1">&raquo;</span>
          <span className={step7 ? "step active mr-1" : "step mr-1"}>
            Step <span className="stepNumber mr-1">7</span>
          </span>
        </div>

        {/* begin: Fullname */}

        {step1 ? (
          <>
            <div className="form-group fv-plugins-icon-container">
              <input
                placeholder="Full name"
                type="text"
                className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                  "fullname"
                )}`}
                name="fullname"
                {...formik.getFieldProps("fullname")}
              />
              {formik.touched.fullname && formik.errors.fullname ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">{formik.errors.fullname}</div>
                </div>
              ) : null}
            </div>
            {/* end: Fullname */}

            {/* begin: Email */}
            <div className="form-group fv-plugins-icon-container">
              <input
                placeholder="Email"
                type="email"
                className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                  "email"
                )}`}
                name="email"
                {...formik.getFieldProps("email")}
              />
              {formik.touched.email && formik.errors.email ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">{formik.errors.email}</div>
                </div>
              ) : null}
            </div>
            {/* end: Email */}

            {/* begin: Username */}
            <div className="form-group fv-plugins-icon-container">
              <input
                placeholder="User name"
                type="text"
                className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                  "username"
                )}`}
                name="username"
                {...formik.getFieldProps("username")}
              />
              {formik.touched.username && formik.errors.username ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">{formik.errors.username}</div>
                </div>
              ) : null}
            </div>
            {/* end: Username */}

            {/* begin: Username */}
            <div className="form-group fv-plugins-icon-container">
              <input
                placeholder="Numero di telefono"
                type="number"
                className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                  "number_phone"
                )}`}
                name="number_phone"
                {...formik.getFieldProps("number_phone")}
              />
              {formik.touched.number_phone && formik.errors.number_phone ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">
                    {formik.errors.number_phone}
                  </div>
                </div>
              ) : null}
            </div>
            {/* end: Username */}

            {/* begin: Password */}
            <div className="form-group fv-plugins-icon-container">
              <input
                placeholder="Password"
                type="password"
                className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                  "password"
                )}`}
                name="password"
                {...formik.getFieldProps("password")}
              />
              {formik.touched.password && formik.errors.password ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">{formik.errors.password}</div>
                </div>
              ) : null}
            </div>
            {/* end: Password */}

            {/* begin: Confirm Password */}
            <div className="form-group fv-plugins-icon-container">
              <input
                placeholder="Confirm Password"
                type="password"
                className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                  "changepassword"
                )}`}
                name="changepassword"
                {...formik.getFieldProps("changepassword")}
              />
              {formik.touched.changepassword && formik.errors.changepassword ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">
                    {formik.errors.changepassword}
                  </div>
                </div>
              ) : null}
            </div>
            {/* end: Confirm Password */}
            <div className="d-flex justify-content-center">
              <button
                disabled={
                  formik.values.fullname === "" &&
                  formik.values.password === "" &&
                  formik.values.changepassword === "" &&
                  formik.values.username === ""
                }
                type="button"
                className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
                onClick={(e) => handlingStep(0, 1, 0, 0)}
              >
                Avanti
              </button>
            </div>
          </>
        ) : (
          <></>
        )}

        {step2 ? (
          <>
            <h1> I tuoi putipù disponibili: {first_result} </h1>
            <div className="form-group fv-plugins-icon-container d-flex justify-content-center">
              <input
                placeholder="Team Name"
                type="text"
                className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                  "teamName"
                )}`}
                name="teamName"
                {...formik.getFieldProps("teamName")}
              />
              {formik.touched.teamName && formik.errors.teamName ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">{formik.errors.teamName}</div>
                </div>
              ) : null}
            </div>

            <div className="d-flex justify-content-center">
            <button
                onClick={(e) => handlingStep(1, 0, 0, 0)}
                className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
              >
                Indietro
              </button>
              <Button
                disabled={formik.values.teamName === ""}
                onClick={(e) => handlingStep(0, 0, 1, 0)}
                className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
              >
                Avanti
              </Button>
            
            </div>
          </>
        ) : (
          <></>
        )}

        {step3 ? (
          <>
            <h1 className="text-center">Seleziona un Gagliardetto</h1>
            <h1 className="text-center">
              {" "}
              I tuoi putipù disponibili: {first_result}{" "}
            </h1>
            <h4 className="text-center" style={{ color: "red" }}>
              {" "}
              ⚠️ Il jolly che puoi scegliere deve essere un membro della tua
              quadriglia ⚠️
            </h4>
            {selectCheckJolly == null ? (
              <></>
            ) : (
              <h1 className="text-center">
                {selectCheckJolly != null ? "Hai scelto il tuo Jolly" : ""}
              </h1>
            )}
            <div>
              {gagliardettoPost.map((e) => {
                return (
                  <>
                    <Card id={e.id_players} className="mb-5 mt-5">
                      <div className=" d-flex  justify-content-around">
                        <Card.Img
                          variant="top"
                          style={{
                            borderRadius: "50%",
                            width: "35%",
                            border: "gray solid 1px",
                            marginTop: "5px",
                          }}
                          src={e.picture == null ? "" : e.picture}
                        />
                        <Card.Body>
                          <Card.Title>
                            Nome: {e.name} <br /> Valore: {e.value}
                          </Card.Title>
                          <Form>
                            <Form.Check
                              type="checkbox"
                              name={e.id_players}
                              id={e.id_players}
                              value={e.value}
                              onChange={handleCheck}
                              checked={e.id_players === selectCheck}
                              label="Scegli elemento"
                            />
                            <Form.Check
                              type="checkbox"
                              label="Nomina come Jolly"
                              id={e.id_players}
                              name={e.id_players}
                              value={e.id_players}
                              disabled={jolly === true}
                              onChange={handleCheckJolly}
                              checked={
                                e.id_players === selectCheckJolly &&
                                e.id_players === selectCheck
                              }
                            />
                          </Form>
                        </Card.Body>
                      </div>
                    </Card>
                  </>
                );
              })}
            </div>
            <div className="d-flex justify-content-center">

            <button
                onClick={(e) => handlingStep(0, 1, 0, 0)}
                className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
              >
                Indietro
              </button>
              <Button
                onClick={(e) => handlingStep(0, 0, 0, 1)}
                className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
                disabled={selectCheck == null}
              >
                Avanti
              </Button>

             
            </div>
          </>
        ) : (
          <></>
        )}
        {step4 ? (
          <>
            <h1 className="text-center">Seleziona un Maestro</h1>
            <h1> I tuoi putipù disponibili: {first_result} </h1>
            <h4 className="text-center" style={{ color: "red" }}>
              {" "}
              ⚠️ Il jolly deve essere uno dei cinque elementi scelti della
              fanatsquadra ⚠️
            </h4>
            {selectCheckJolly == null ? (
              <></>
            ) : (
              <h1>
                {selectCheckJolly != null ? "Hai scelto il tuo Jolly" : ""}
              </h1>
            )}
            {maestroPost.map((e) => {
              return (
                <>
                  <Card id={e.id_players} className="mb-5 mt-5">
                    <div className=" d-flex  justify-content-around">
                      <Card.Img
                        variant="top"
                        style={{
                          borderRadius: "50%",
                          width: "35%",
                          border: "black solid 1px",
                          marginTop: "5px",
                        }}
                        src={e.picture == null ? "" : e.picture}
                      />
                      <Card.Body>
                        <Card.Title>
                          Nome: {e.name} <br /> Valore: {e.value}
                        </Card.Title>
                        <Form>
                          <Form.Check
                            type="checkbox"
                            name={e.id_players}
                            id={e.id_players}
                            value={e.value}
                            onChange={handleCheckMaestro}
                            checked={e.id_players === selectCheckMaestro}
                            label="Scegli elemento"
                          />
                          <Form.Check
                            type="checkbox"
                            label="Nomina come Jolly"
                            id={e.id_players}
                            disabled={jolly === true}
                            name={e.id_players}
                            value={e.id_players}
                            onChange={handleCheckJolly}
                            checked={
                              e.id_players === selectCheckJolly &&
                              e.id_players === selectCheckMaestro
                            }
                          />
                        </Form>
                      </Card.Body>
                    </div>
                  </Card>
                </>
              );
            })}
            {first_result < 0 ? (
              <>
                <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
                  <div className="alert-text text-center">
                    Hai superato i putipù disponibili:{" "}
                    <strong>{first_result}</strong>
                  </div>
                </div>
              </>
            ) : (
              <></>
            )}
            <div className="d-flex justify-content-center">
            
              <button
                onClick={(e) => handlingStep(0, 0, 1, 0, 0, 0)}
                className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
                disabled={first_result < 0}
              >
                Indietro
              </button>

              <Button
                onClick={(e) => handlingStep(0, 0, 0, 0, 1, 0)}
                className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
                disabled={selectCheckMaestro == null}
              >
                Avanti
              </Button>
            </div>
          </>
        ) : (
          <></>
        )}

        {step5 ? (
          <>
            <h1 className="text-center">Seleziona una Scenografia</h1>
            <h1> I tuoi putipù disponibili: {first_result} </h1>
            <h4 className="text-center" style={{ color: "red" }}>
              {" "}
              ⚠️ Il jolly deve essere uno dei cinque elementi scelti della
              fanatsquadra ⚠️
            </h4>
            {selectCheckJolly == null ? (
              <></>
            ) : (
              <h1>
                {selectCheckJolly != null ? "Hai scelto il tuo Jolly" : ""}
              </h1>
            )}
            {scenografiaPost.map((e) => {
              return (
                <>
                  <Card id={e.id_players} className="mb-5 mt-5">
                    <div className=" d-flex  justify-content-around">
                      <Card.Img
                        variant="top"
                        style={{
                          borderRadius: "50%",
                          width: "35%",
                          border: "black solid 1px",
                          marginTop: "5px",
                        }}
                        src={e.picture == null ? "" : e.picture}
                      />
                      <Card.Body>
                        <Card.Title>
                          Nome: {e.name} <br /> Valore: {e.value}
                        </Card.Title>
                        <Form>
                          <Form.Check
                            type="checkbox"
                            name={e.id_players}
                            id={e.id_players}
                            value={e.value}
                            onChange={handleCheckScenografia}
                            checked={e.id_players === selectCheckScenografia}
                            label="Scegli elemento"
                          />
                          <Form.Check
                            type="checkbox"
                            label="Nomina come Jolly"
                            id={e.id_players}
                            disabled={jolly === true}
                            name={e.id_players}
                            value={e.id_players}
                            onChange={handleCheckJolly}
                            checked={
                              e.id_players === selectCheckJolly &&
                              e.id_players === selectCheckScenografia
                            }
                          />
                        </Form>
                      </Card.Body>
                    </div>
                  </Card>
                </>
              );
            })}
            {first_result < 0 ? (
              <>
                <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
                  <div className="alert-text text-center">
                    Hai superato i putipù disponibili:{" "}
                    <strong>{first_result}</strong>
                  </div>
                </div>
              </>
            ) : (
              <></>
            )}
            <div className="d-flex justify-content-center">
           
              <button
                onClick={(e) => handlingStep(0, 0, 0, 1, 0, 0)}
                className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
              >
                Indietro
              </button>

              <Button
                onClick={(e) => handlingStep(0, 0, 0, 0, 0, 1)}
                className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
                disabled={selectCheckScenografia == null && first_result < 0}
              >
                Avanti
              </Button>
            </div>
          </>
        ) : (
          <></>
        )}

        {step6 ? (
          <>
            <h1 className="text-center">Seleziona una Musica</h1>
            <h1> I tuoi putipù disponibili: {first_result} </h1>
            <h4 className="text-center" style={{ color: "red" }}>
              {" "}
              ⚠️ Il jolly deve essere uno dei cinque elementi scelti della
              fanatsquadra ⚠️
            </h4>
            {selectCheckJolly == null ? (
              <></>
            ) : (
              <h1>
                {selectCheckJolly != null ? "Hai scelto il tuo Jolly" : ""}
              </h1>
            )}
            {musicaPost.map((e) => {
              return (
                <>
                  <Card id={e.id_players} className="mb-5 mt-5">
                    <div className=" d-flex  justify-content-around">
                      <Card.Img
                        variant="top"
                        style={{
                          borderRadius: "50%",
                          width: "35%",
                          border: "black solid 1px",
                          marginTop: "5px",
                        }}
                        src={e.picture == null ? "" : e.picture}
                      />
                      <Card.Body>
                        <Card.Title>
                          Nome: {e.name} <br /> Valore: {e.value}
                        </Card.Title>
                        <Form>
                          <Form.Check
                            type="checkbox"
                            name={e.id_players}
                            id={e.id_players}
                            value={e.value}
                            onChange={handleCheckMusica}
                            checked={e.id_players === selectCheckMusica}
                            label="Scegli elemento"
                          />
                          <Form.Check
                            type="checkbox"
                            label="Nomina come Jolly"
                            id={e.id_players}
                            disabled={jolly === true}
                            name={e.id_players}
                            value={e.value}
                            onChange={handleCheckJolly}
                            checked={
                              e.id_players === selectCheckJolly &&
                              e.id_players === selectCheckMusica
                            }
                          />
                        </Form>
                      </Card.Body>
                    </div>
                  </Card>
                </>
              );
            })}
            {first_result < 0 ? (
              <>
                <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
                  <div className="alert-text text-center">
                    Hai superato i putipù disponibili:{" "}
                    <strong>{first_result}</strong>
                  </div>
                </div>
              </>
            ) : (
              <></>
            )}
            <div className="d-flex justify-content-center">
         
              <button
                onClick={(e) => handlingStep(0, 0, 0, 0, 1, 0, 0)}
                className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
              >
                Indietro
              </button>

              <Button
                onClick={(e) => handlingStep(0, 0, 0, 0, 0, 0, 1)}
                className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
                disabled={selectCheckMusica == null || first_result < 0}
              >
                Avanti
              </Button>
            </div>
          </>
        ) : (
          <></>
        )}

        {step7 ? (
          <>
            <h1 className="text-center">Seleziona un Costume</h1>
            <h1> I tuoi putipù disponibili: {first_result} </h1>
            <h4 className="text-center" style={{ color: "red" }}>
              {" "}
              ⚠️ Il jolly deve essere uno dei cinque elementi scelti della
              fanatsquadra ⚠️
            </h4>
            {selectCheckJolly == null ? (
              <></>
            ) : (
              <h1>
                {selectCheckJolly != null ? "Hai scelto il tuo Jolly" : ""}
              </h1>
            )}
            {/* begin: Terms and Conditions */}
            {constumePost.map((e) => {
              return (
                <>
                  <Card id={e.id_players} className="mb-5 mt-5">
                    <div className=" d-flex  justify-content-around">
                      <Card.Img
                        variant="top"
                        style={{
                          borderRadius: "50%",
                          width: "35%",
                          border: "black solid 1px",
                          marginTop: "5px",
                        }}
                        src={e.picture == null ? "" : e.picture}
                      />
                      <Card.Body>
                        <Card.Title>
                          Nome: {e.name} <br /> Valore: {e.value}
                        </Card.Title>
                        <Form>
                          <Form.Check
                            type="checkbox"
                            name={e.id_players}
                            id={e.id_players}
                            value={e.value}
                            onChange={handleCheckCostume}
                            checked={e.id_players === selectCheckCostume}
                            label="Scegli elemento"
                          />
                          <Form.Check
                            type="checkbox"
                            label="Nomina come Jolly"
                            id={e.id_players}
                            disabled={jolly === true}
                            name={e.id_players}
                            value={e.value}
                            onChange={handleCheckJolly}
                            checked={
                              e.id_players === selectCheckJolly &&
                              e.id_players === selectCheckCostume
                            }
                          />
                        </Form>
                      </Card.Body>
                    </div>
                  </Card>
                </>
              );
            })}
            <div className="form-group">
              <label className="checkbox">
                <input
                  type="checkbox"
                  name="acceptTerms"
                  className="m-1"
                  {...formik.getFieldProps("acceptTerms")}
                />
                Accetto termini e condizioni
                <span />
              </label>
              {formik.touched.acceptTerms && formik.errors.acceptTerms ? (
                <div className="fv-plugins-message-container">
                  <div className="fv-help-block">
                    {formik.errors.acceptTerms}
                  </div>
                </div>
              ) : null}
            </div>
            {/* end: Terms and Conditions */}
            {first_result < 0 ? (
              <>
                <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
                  <div className="alert-text text-center">
                    Hai superato i putipù disponibili:{" "}
                    <strong>{first_result}</strong>
                  </div>
                </div>
              </>
            ) : (
              <></>
            )}

            {formik.values.acceptTerms ? (
              <>
                <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
                  <div className="alert-text text-center">
                    <strong>
                      Al completamento della registrazione verrai indirizzato
                      alla pagina di Log-In, <br /> dove potrai inserire E-mail
                      e Password scelti per accedere{" "}
                    </strong>
                  </div>
                </div>
              </>
            ) : (
              <></>
            )}
            <div className="form-group d-flex flex-wrap flex-center">
          
              <button
                type="button"
                className="btn btn-light-primary font-weight-bold px-9 py-4 my-3 mx-4"
                onClick={(e) => handlingStep(0, 0, 0, 0, 1, 0)}
                style={{ background: "#2f2d77", color: "#ffffff" }}
              >
                Indietro
              </button>

              <button
                type="submit"
                disabled={
                  formik.isSubmitting ||
                  !formik.isValid ||
                  !formik.values.acceptTerms ||
                  selectCheckCostume == null ||
                  first_result < 0
                }
                className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
                style={{ background: "#2f2d77", color: "#ffffff" }}
              >
                <span>Iscriviti</span>
                {loading && (
                  <span className="ml-3 spinner spinner-white"></span>
                )}
              </button>

            </div>
         
          </>
        ) : (
          <></>
        )}
      </form>
    </div>
  );
}

export default injectIntl(connect(null, auth.actions)(Registration));
