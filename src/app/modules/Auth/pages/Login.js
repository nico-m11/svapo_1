/* eslint-disable no-restricted-imports */
/* eslint-disable no-script-url,jsx-a11y/anchor-is-valid */
import React, { useState } from "react";
import { useSelector, shallowEqual, connect, useDispatch } from "react-redux";
import { Link, useHistory } from "react-router-dom";
import { useFormik } from "formik";
import * as Yup from "yup";
import { FormattedMessage, injectIntl } from "react-intl";
import * as auth from "../_redux/authRedux";
import { login } from "../_redux/authCrud";
import Button from "react-bootstrap/Button";
import { FormatAlignCenter, LaptopWindows } from "@material-ui/icons";
import Modal from "react-bootstrap/Modal";
import Accordion from "react-bootstrap/Accordion";
import config from "../../../../config/config";


/*
  INTL (i18n) docs:
  https://github.com/formatjs/react-intl/blob/master/docs/Components.md#formattedmessage
*/

/*
  Formik+YUP:
  https://jaredpalmer.com/formik/docs/tutorial#getfieldprops
*/

const initialValues = {
  email: "",
  password: "",
};

function Login(props) {
  const { intl } = props;
  //const config = useSelector((state) => state.authConfig.config);
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);
  const LoginSchema = Yup.object().shape({
    email: Yup.string()
      .email("Wrong email format")
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
  });

  const history = useHistory();

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
  const formik = useFormik({
    initialValues,
    validationSchema: LoginSchema,
    onSubmit: (values, { setStatus, setSubmitting }) => {
      enableLoading();
      setTimeout(() => {
        const requestOptions = {
          headers: {
            Authorization:
              "271c4d716fcf0e9555b51cffed666b4321f98f7f8bbeb9ae8bfc67752b4db8a2",
          },
          method: "POST",
          body: JSON.stringify({
            email: values.email,
            password: values.password,
          }),
        };
        fetch(config.apiUrl + "users/LoginUser.php", requestOptions)
          .then((response) => response.json())
          .then((result) => {
            if (result.length > 0) {
              dispatch(auth.actions.login(result[0].authToken));
              dispatch(auth.actions.setUser(result[0]));
              window.location.replace("/");
            } else {
              if (result < 0) {
                disableLoading();
                setSubmitting(false);
                setStatus(
                  intl.formatMessage({
                    id: "AUTH.VALIDATION.USER_NOT_ACTIVE",
                  })
                );
              } else {
                disableLoading();
                setSubmitting(false);
                setStatus(
                  intl.formatMessage({
                    id: "AUTH.VALIDATION.INVALID_LOGIN",
                  })
                );
              }
            }
          })
          .catch((error) => {
            disableLoading();
            setSubmitting(false);
            setStatus(
              intl.formatMessage({
                id: "AUTH.VALIDATION.INVALID_LOGIN",
              })
            );
          });
      }, 1000);
    },
  });

  function Regolamento() {
    const [show, setShow] = useState(false);

    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);

    return (
      <>
        <Button variant="outline-secondary ml-5 mb-5" onClick={handleShow}>
          <i class="fa fa-newspaper" style={{ color: "#2f2d77" }}></i> <br />
          Regolamento
        </Button>

        <Modal
          show={show}
          onHide={handleClose}
          backdrop="static"
          keyboard={false}
        >
          <Modal.Header closeButton>
            <Modal.Title>Regolamento</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <ul>
              <li>
                Ogni giocatore ha a disposizione 75 Putipù* per acquistare 5
                degli elementi che compongono la fantaquadriglia: 1
                Gagliardetto, 1 Maestro, 1 Sfilata di costumi, 1 Banda musicale,
                1 Scenografia e coreografia. È obbligatorio schierare una
                squadra completa di 5 elementi.
              </li>
              <li>
                Per “maestro” si prenderanno in considerazione i nomi elencati
                all’interno del sito o chiunque altro dovesse (per motivi di
                salute o altro) farne le veci dirigendo la banda musicale.
              </li>
              <li>
                Il costo dei singoli componenti sarà inversamente proporzionale
                alle classifiche di categoria della precedente edizione. (Es: Il
                maestro del Gruppo Storico Quadriglia degli Studenti si è
                posizionato al primo posto nella più recente classifica di
                miglior maestro. Il prezzo per acquistare il maestro del Gruppo
                Storico Quadriglia degli Studenti sarà allora di 27 crediti. Il
                costo del secondo classificato sarà 24 crediti, il costo del
                terzo 21 crediti e così via).
              </li>
              <li>
                È obbligatorio nominare “JOLLY” uno degli elementi acquistati.
                L’elemento Jolly raddoppierà i punti guadagnati dall’elemento
                scelto.{" "}
              </li>
              <li>
                È possibile iscrivere la squadra entro venerdì 3 Febbraio 2023
                alle ore 23:59.
              </li>
              <li>
                Ogni elemento ha la possibilità di raccogliere o perdere punti
                durante la manifestazione attraverso bonus e malus scelti dalla
                Fondazione connessi alle azioni da essi svolti sul palco.
              </li>
              <li>
                I bonus e i malus per ogni elemento verranno attribuiti dagli
                organizzatori della Fondazione Carnevale di Palma Campania che
                valuterà l'accaduto in modo professionale, incondizionato e
                obiettivo.
              </li>
              <li>
                L'assegnazione dei bonus e dei malus si applica agli elementi
                nelle giornate di domenica 19 febbraio (giornata della
                messinscena) e martedì 21 febbraio (giornata dei canzonieri) e
                riguarderanno esclusivamente le performances sul palco (tutto
                ciò che avviene prima e dopo il palco non verrà conteggiato).
              </li>
              <li>
                Vince la competizione chi, terminata la kermesse, ha totalizzato
                il maggior numero di punti.
              </li>
              <li>
                I Bonus e i Malus saranno direttamente verificabili nel momento
                in cui le Quadriglie si esibiranno sul Palco Centrale la
                Domenica della Messinscena e il Martedì dei Canzonieri. Gli
                altri bonus/malus saranno attribuiti sulla base dei premi
                assegnati, delle votazioni dei giurati e dal conteggio delle
                penalità.
              </li>
              <li>
                Non potranno essere aggiunti ulteriori BONUS E MALUS rispetto a
                quelli pubblicati prima dell’apertura delle iscrizioni.
              </li>
              <li>
                Il regolamento potrà essere soggetto a interpretazioni e/o
                chiarimenti da parte della FPFC** anche durante la kermesse.
              </li>
            </ul>
            <p>
              *Putipù: moneta ufficiale del fantacarnevale stampato dalla zecca
              di paese
            </p>
            <p>** FPFC: Federazione Palmese Fanta Carnevale</p>
            <p style={{ color: "#CF2F9E" }}>
              In caso di vittoria in ex aequo di più utenti, il vincitore del
              premio in palio verrà eletto a sorte tra i vincitori appaiati allo
              stesso punteggio
            </p>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={handleClose}>
              Chiudi
            </Button>
          </Modal.Footer>
        </Modal>
      </>
    );
  }

  function Faq() {
    const [show, setShow] = useState(false);

    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);

    return (
      <>
        <Button variant="outline-secondary ml-5 mb-5" onClick={handleShow}>
          <i class="fas fa-question" style={{ color: "#2f2d77" }}></i> <br />
          Faq
        </Button>

        <Modal
          show={show}
          onHide={handleClose}
          backdrop="static"
          keyboard={false}
        >
          <Modal.Header closeButton>
            <Modal.Title>Faq</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <p>Cos’è il Fantacarnevale?</p>
            <p>
              FantaCarnevale è il fantasy game basato sul Carnevale più bello
              d’Italia. Componi la tua squadra scegliendo 5 elementi, uno per
              ogni categoria in gara, nomina un Jolly e in base a cosa faranno i
              tuoi elementi guadagnerai o perderai punti.
            </p>
            <p>Come faccio a creare la mia squadra?</p>
            <p>
              Crea un account, segui la procedura per completare la
              registrazione e componi la tua squadra. Dovrai comporre una
              squadra formata da 5 artisti e nominarne uno Jolly. Attenzione
              però a non sforare il budget di 75 Putipù, anche se sarà il
              sistema a porti un limite.
            </p>
            <p>Cosa sono i Putipù?</p>
            <p>
              I Putipù sono la moneta ufficiale del FantaCarnevale. Ogni
              giocatore ha a disposizione 75 Putipù per comporre la propria
              squadra. Il sito vi aiuterà a contare quanti ve ne restano dopo
              ogni acquisto.
            </p>
            <p>A cosa serve scegliere un elemento jolly?</p>
            <p>
              L’elemento Jolly ti dà l’opportunità di raddoppiare bonus e malus
              conquistati dall’elemento da te scelto. Sceglilo con cura,
              potrebbe fare la differenza!
            </p>
            <p>
              Come faccio a sapere se i componenti della mia squadra hanno
              guadagnato dei punti?
            </p>
            <p>
              Ti basterà leggere il regolamento che contiene la lista completa
              delle azioni che fanno guadagnare (bonus) o perdere (malus) punti.
              Pubblicheremo spesso degli esempi pratici, segui le nostre pagine
              social e sarà tutto chiarissimo. Durante la settimana di Carnevale
              dovrai solo accedere alla tua dashboard e, una volta assegnati i
              bonus e i malus, troverai il recap con i punteggi delle tue
              squadre.
            </p>
            <p>Ok, ho creato la mia prima squadra. E adesso?</p>
            <p>
              La squadra che hai appena creato è quella che sfiderà gli altri
              utenti nella classifica generale del FantaCarnevale. Adesso puoi
              creare le tue leghe o partecipare a delle leghe già esistenti.
            </p>
            <p>Cosa sono le Leghe?</p>
            <p>
              Le Leghe sono dei campionati separati e paralleli alla classifica
              generale. In parole povere sono delle classifiche personalizzabili
              (nome, miniatura, copertina ecc...) in cui compariranno solo ed
              esclusivamente determinate squadre. Potrebbe essere il modo
              migliore per creare un torneo personalizzato tra amici, parenti,
              colleghi o anche community più grandi.
            </p>
            <p>
              Non sono d'accordo con l'assegnazione di un bonus/malus, cosa
              posso fare?
            </p>
            <p>
              Accettalo e bevici su. Altrimenti, se proprio ne senti il bisogno,
              scrivici su instagram alla pagina Fantacarnevale_2023 e valuteremo
              il tutto al VAR. Non vi assicuriamo niente però, lo sapete come
              sono gli arbitri in Italia.
            </p>
            <p>Posso creare una squadra con meno di 5 elementi?</p>
            <p>
              No. Può una quadriglia sfilare senza banda, costumi o maestro?
            </p>
            <p>Cosa serve per partecipare? Quanto costa?</p>
            <p>
              È tutto assolutamente gratis. Ti basta solo un indirizzo email
              valido. Certo, se poi volessi essere così gentile da seguirci
              anche sui nostri social te ne saremmo molto grati, poi fai tu
              liberamente. Potrai avere aggiornamenti sui punteggi della tua
              squadra e seguire tutte le novità.
            </p>
            <p style={{ color: "#CF2F9E" }}>
              Cosa succede se mi classifico primo insieme ad altri?
            </p>
            <p style={{ color: "#CF2F9E" }}>
              In caso di vittoria a pari merito il vincitore del premio in palio
              verrà sorteggiato
            </p>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={handleClose}>
              Chiudi
            </Button>
          </Modal.Footer>
        </Modal>
      </>
    );
  }
  function Bonus() {
    const [show, setShow] = useState(false);

    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);

    return (
      <>
        <Button variant="outline-secondary ml-5 mb-5" onClick={handleShow}>
          <i class="fas fa-thumbs-up" style={{ color: "#2f2d77" }}></i>
          <i class="fas fa-thumbs-down" style={{ color: "#2f2d77" }}></i>
          <br />
          Bonus/Malus
        </Button>

        <Modal
          show={show}
          onHide={handleClose}
          backdrop="static"
          keyboard={false}
        >
          <Modal.Header closeButton>
            <Modal.Title>Bonus/Malus</Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <div className="App align-center">
              <a
                href="/pdf/Bonus_Malus_del_Fantacarnevale_2023_aggiornato_a_LUN_23_gennaio.pdf"
                download
              >
                <button
                  className="btn  font-weight-bold px-9 py-4 my-3 mx-4 "
                  style={{ background: "#2f2d77", color: "#ffffff" }}
                >
                  Dowload Bonus/Malus
                </button>
              </a>
            </div>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={handleClose}>
              Chiudi
            </Button>
          </Modal.Footer>
        </Modal>
      </>
    );
  }

  return (
    <div className="login-form login-signin" id="kt_login_signin_form">
      {/* begin::Head */}
      <div className="d-flex justify-content-center">
        {/* <Button variant="outline-secondary mr-5 mb-5"> */}
        <Regolamento />
        {/* <Link to="/faq" className="navi-item">
            <i class="fa fa-newspaper"></i>
            <br />
            Regolamento
          </Link>
        </Button> */}
        {/* <Button variant="outline-secondary ml-5 mb-5">
          <i class="fas fa-question"></i>
          <br />
          FAQ
        </Button> */}
        <Faq />
        <Bonus />
      </div>
      <div className="text-center mb-10 mb-lg-20">
        <h3 className="font-size-h1">
          <FormattedMessage id="AUTH.LOGIN.TITLE" />
        </h3>
        <p className="text-muted font-weight-bold">
          Inserisci la tua email e password
        </p>
      </div>
      {/* end::Head */}
      {/*begin::Form*/}
      <form
        onSubmit={formik.handleSubmit}
        className="form fv-plugins-bootstrap fv-plugins-framework"
      >
        {formik.status ? (
          <div className="mb-10 alert alert-custom alert-light-danger alert-dismissible">
            <div className="alert-text font-weight-bold">{formik.status}</div>
          </div>
        ) : (
          <></>
          // <div className="mb-10 alert alert-custom alert-light-info alert-dismissible">
          //   <div className="alert-text text-center">
          //     Il sistema sarà attivo <strong>il 24/01/2023</strong>
          //   </div>
          // </div>
        )}

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
        <div className="form-group d-flex flex-wrap justify-content-between align-items-center">
          {/* <Link
            to="/auth/forgot-password"
            className="text-dark-50 text-hover-primary my-3 mr-2"
            id="kt_login_forgot"
          >
            <FormattedMessage id="AUTH.GENERAL.FORGOT_BUTTON" />
          </Link> */}
          <button
            id="kt_login_signin_submit"
            type="submit"
            disabled={formik.isSubmitting}
            className={`btn  font-weight-bold px-9 py-4 my-3`}
            style={{ background: "#2f2d77", color: "#ffffff" }}
          >
            <span>Accedi</span>
            {loading && <span className="ml-3 spinner spinner-white"></span>}
          </button>
        </div>
      </form>
      {/*end::Form*/}
    </div>
  );
}

export default injectIntl(connect(null, auth.actions)(Login));
