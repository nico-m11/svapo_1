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
  path = "http://localhost/svapo_1/public";
} else {
  path = window.location.origin;
}

const arrConfig = [
  {
    apiUrl: path + "/inc/api/",
    siteUrl: window.location.origin + "/dashboard",
    sitePath: window.location.origin,
    browser: GetBrowser(),
    keyApi: "271c4d716fcf0e9555b51cffed666b4321f98f7f8bbeb9ae8bfc67752b4db8a2",
  },
];

export default arrConfig[0];

export const Pad = (n, width, z) => {
  z = z || "0";
  n = n + "";
  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
};

export const roles = {
  admin: 4,
  hr: 3,
};

