// import moment from "moment-timezone";
// import "moment/locale/it";

//moment.locale("it");

// const TimenozeInMinutes = () => {
//   // var tz_guess = moment.tz.guess();
//   // var tz_tm = moment.tz(tz_guess).format("Z");

//   var sign = tz_tm.charAt(0);
//   var result = tz_tm.substring(1) + ":00";
//   var hms = result; // your input string
//   var a = hms.split(":"); // split it at the colons
//   // Hours are worth 60 minutes.
//   var minutes = +a[0] * 60 + +a[1];

//   return sign + "" + minutes;
// };

function GetBrowser() {
  var name;
  if (
    (navigator.userAgent.indexOf("Opera") ||
      navigator.userAgent.indexOf("OPR")) !== -1
  ) {
    name = "Opera";
  } else if (navigator.userAgent.indexOf("Chrome") !== -1) {
    name = "Chrome";
  } else if (navigator.userAgent.indexOf("Safari") !== -1) {
    name = "Safari";
  } else if (navigator.userAgent.indexOf("Firefox") !== -1) {
    name = "Firefox";
  } else if (
    navigator.userAgent.indexOf("MSIE") !== -1 ||
    !!document.documentMode === true
  ) {
    //IF IE > 10
    name = "IE";
  } else {
    name = "unknown";
  }

  return name;
}

var path;
if (window.location.origin.includes("localhost")) {
  path = "http://localhost/svapo/public";
} else {
  path = window.location.origin;
}

const arrConfig = [
  {
    // apiUrl:"https://localhost/crurated/auction/api/",
    // siteUrl:"http://localhost:3000/dashboard",
    apiUrl: path + "/inc/api/",
    siteUrl: window.location.origin + "/dashboard",
    sitePath: window.location.origin,
    //timezone: TimenozeInMinutes(),
    browser: GetBrowser(),
    keyApi: "271c4d716fcf0e9555b51cffed666b4321f98f7f8bbeb9ae8bfc67752b4db8a2",
  },
];

export default arrConfig[0];

// Description for auction
// export const SetDataTimeZone = (date, format) => {
//   function RefreshDataTimeZone(date) {
//     var dateType = date;

//     var event = new Date(dateType);

//     //event.setMinutes(TimenozeInMinutes());

//     //var dateSet = moment(event).format(format);

//     return dateSet;
//   }

//   return RefreshDataTimeZone(date);
// };

// from a full date-time-offset ISO8601/RFC3339 value
// console.log(SetDataTimeZone('2017/05/22 02:00', "YYYY-MM-DD HH:mm:ss"))

export const Pad = (n, width, z) => {
  z = z || "0";
  n = n + "";
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
};

export const roles = {
  admin: 4,
  hr: 3,
  dipendente: 1,
};

export const modules = {
  officina: 1,
  customerCare: 2,
  venditori: 3,
};
