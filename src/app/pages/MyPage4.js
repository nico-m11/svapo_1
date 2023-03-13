/* eslint-disable no-restricted-imports */
// React bootstrap table next =>
// DOCS: https://react-bootstrap-table.github.io/react-bootstrap-table2/docs/
// STORYBOOK: https://react-bootstrap-table.github.io/react-bootstrap-table2/storybook/index.html
import React, { useEffect, useMemo, useState } from "react";
import { useSelector, shallowEqual, useDispatch } from "react-redux";
import { useFormik } from "formik";
import * as Yup from "yup";
import config from "../../config/config";
import { setDefaultOptions } from "date-fns";

const initialValues = {
  email: "",
  username: "",
  password: "",
  name: "",
  role: 0,
};

export function MyPage4(props) {
  const user = useSelector((state) => state.auth.user);
  const { intl } = props;
  const [loading, setLoading] = useState(false);

  const RegistrationSchema = Yup.object().shape({
    username: Yup.string()
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required("Inserisci Username"),
    email: Yup.string()
      .email("Wrong email format")
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required("Inserisci E-mail"),
    name: Yup.string()
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required("Inserisci Nome"),
    password: Yup.string()
      .min(3, "Minimum 3 symbols")
      .max(50, "Maximum 50 symbols")
      .required("Inserisci una password"),
  });

  const getInputClasses = (fieldname) => {
    if (formik.touched[fieldname] && formik.errors[fieldname]) {
      return "is-invalid";
    }

    if (formik.touched[fieldname] && !formik.errors[fieldname]) {
      return "is-valid";
    }

    return "";
  };

  const formik = useFormik({
    initialValues,
    validationSchema: RegistrationSchema,
    onSubmit: (values, { setStatus, setSubmitting}) => {
      setLoading(true);
      const requestOptions = {
        headers: {
          Authorization:
            "271c4d716fcf0e9555b51cffed666b4321f98f7f8bbeb9ae8bfc67752b4db8a2",
        },
        method: "POST",
        body: JSON.stringify({
          email: values.email,
          password: values.password,
          name: values.name,
          username: values.username,
          role: values.role
        }),
      };

      fetch(config.apiUrl + "users/CreateUser.php", requestOptions)
        .then((response) => response.json())
        .then((result) => {
          console.log(result);
        })
        .then(({ data: { authToken } }) => {
          props.register(authToken);
          setLoading(false);
          setSubmitting(false);
          window.location.href = "/";
        })
        .catch(() => {
          setLoading(false);
          setSubmitting(false);
        });
    },
  });
// manca solo da fare, la select, per farla crea taballa il quale crei un funzione che poi richiamo in get per poi
// in front la cicli con il array map e trovi che il select si popola in auto e cosi potremo prendere io valore
  return (
    <>
      <div className="login-form login-signin" style={{ display: "block" }}>
        <div className="text-center mb-10 mb-lg-20">
          <h3 className="font-size-h1">Crea Utente</h3>{" "}
        </div>
        <form
          id="kt_login_signin_form"
          className="form fv-plugins-bootstrap fv-plugins-framework animated animate__animated animate__backInUp"
          onSubmit={formik.handleSubmit}
        >
          {/* begin: Fullname */}
          <div className="form-group fv-plugins-icon-container">
            <select
              className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                "role"
              )}`}
              aria-label=".form-select-sm example"
            >
              <option selected>Seleziona Ruolo</option>
              <option value='one'>One</option>
              <option value='two'>Two</option>
            </select>
            {formik.touched.role && formik.errors.role ? (
              <div className="fv-plugins-message-container">
                <div className="fv-help-block">{formik.errors.role}</div>
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
              placeholder="Nome"
              type="text"
              className={`form-control form-control-solid h-auto py-5 px-6 ${getInputClasses(
                "name"
              )}`}
              name="name"
              {...formik.getFieldProps("name")}
            />
            {formik.touched.name && formik.errors.name ? (
              <div className="fv-plugins-message-container">
                <div className="fv-help-block">{formik.errors.name}</div>
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

          <div className="form-group d-flex flex-wrap flex-center">
            <button
              type="submit"
              disabled={formik.isSubmitting || !formik.isValid}
              className="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4"
              style={{ background: "#2f2d77", color: "#ffffff" }}
            >
              <span>Iscriviti</span>
              {loading && <span className="ml-3 spinner spinner-white"></span>}
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
