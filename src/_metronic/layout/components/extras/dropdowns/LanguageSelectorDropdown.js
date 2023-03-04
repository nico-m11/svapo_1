/* eslint-disable no-script-url,jsx-a11y/anchor-is-valid */
import React from "react";
import clsx from "clsx";
import { Dropdown } from "react-bootstrap";
import { OverlayTrigger, Tooltip } from "react-bootstrap";
import { toAbsoluteUrl } from "../../../../_helpers";
import { useLang, setLanguage } from "../../../../i18n";
import { DropdownTopbarItemToggler } from "../../../../_partials/dropdowns";

const languages = [
  {
    lang: "en",
    name: "English",
    flag: toAbsoluteUrl("/media/svg/flags/226-united-states.svg"),
  },
  {
    lang: "zh",
    name: "Mandarin",
    flag: toAbsoluteUrl("/media/svg/flags/015-china.svg"),
  },
  {
    lang: "es",
    name: "Spanish",
    flag: toAbsoluteUrl("/media/svg/flags/128-spain.svg"),
  },
  {
    lang: "ja",
    name: "Japanese",
    flag: toAbsoluteUrl("/media/svg/flags/063-japan.svg"),
  },
  {
    lang: "de",
    name: "German",
    flag: toAbsoluteUrl("/media/svg/flags/162-germany.svg"),
  },
  {
    lang: "fr",
    name: "French",
    flag: toAbsoluteUrl("/media/svg/flags/195-france.svg"),
  },
];

export function LanguageSelectorDropdown() {
  const lang = useLang();
  const currentLanguage = languages.find((x) => x.lang === lang);
  return (
   <></>
  );
}
