import React, { useEffect, useState, useMemo } from "react";
import clsx from "clsx";
import { Dropdown, OverlayTrigger, Tooltip } from "react-bootstrap";
import SVG from "react-inlinesvg";
import objectPath from "object-path";
import { useHtmlClassService } from "../../../../_core/MetronicLayout";
import { SearchResult } from "./SearchResult";
import { toAbsoluteUrl } from "../../../../../_helpers";
import { DropdownTopbarItemToggler } from "../../../../../_partials/dropdowns";
const fakeData = [
  {
    type: 0,
    text: "Reports",
  },
  {
    type: 1,
    text: "AirPlus Requirements",
    description: "by Grog John",
    iconImage: toAbsoluteUrl("/media/files/doc.svg"),
  },
  {
    type: 1,
    text: "TechNav Documentation",
    description: "by Mary Brown",
    iconImage: toAbsoluteUrl("/media/files/pdf.svg"),
  },
  {
    type: 1,
    text: "All Framework Docs",
    description: "by Grog John",
    iconImage: toAbsoluteUrl("/media/files/zip.svg"),
  },
  {
    type: 1,
    text: "AirPlus Requirements",
    description: "by Tim Hardy",
    iconImage: toAbsoluteUrl("/media/files/xml.svg"),
  },
  {
    text: "Customers",
    type: 0,
  },
  {
    type: 1,
    text: "Jimmy Curry",
    description: "Software Developer",
    iconImage: toAbsoluteUrl("/media/users/300_11.jpg"),
  },
  {
    type: 1,
    text: "Milena Gibson",
    description: "UI Designer",
    iconImage: toAbsoluteUrl("/media/users/300_16.jpg"),
  },
  {
    type: 1,
    text: "Stefan JohnStefan",
    description: "Marketing Manager",
    iconImage: toAbsoluteUrl("/media/users/300_22.jpg"),
  },
  {
    type: 1,
    text: "Anna Strong",
    description: "Software Developer",
    iconImage: toAbsoluteUrl("/media/users/300_5.jpg"),
  },
  {
    type: 0,
    text: "Files",
  },
  {
    type: 1,
    text: "2 New items submitted",
    description: "Marketing Manager",
    iconClassName: "flaticon2-box font-danger",
  },
  {
    type: 1,
    text: "79 PSD files generated",
    description: "by Grog John",
    iconClassName: "flaticon-psd font-brand",
  },
  {
    type: 1,
    text: "$2900 worth products sold",
    description: "Total 234 items",
    iconClassName: "flaticon2-supermarket font-warning",
  },
  {
    type: 1,
    text: "4 New items submitted",
    description: "Marketing Manager",
    iconClassName: "flaticon-safe-shield-protection font-info",
  },
];

export function SearchDropdown() {
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState(null);
  const [searchValue, setSearchValue] = useState("");
  let timeoutId;

  const clearTimeout = () => {
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = undefined;
    }
  };

  const handleSearchChange = (event) => {
    setData(null);
    setSearchValue(event.target.value);

    if (event.target.value.length > 2) {
      clearTimeout();

      setLoading(true);

      // simulate getting search result
      timeoutId = setTimeout(() => {
        setData(fakeData);
        setLoading(false);
      }, 500);
    }
  };

  const clear = () => {
    setData(null);
    setSearchValue("");
  };

  useEffect(() => {
    return () => {
      clearTimeout();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const uiService = useHtmlClassService();
  const layoutProps = useMemo(() => {
    return {
      offcanvas:
        objectPath.get(uiService.config, "extras.search.layout") ===
        "offcanvas",
    };
  }, [uiService]);

  return (
    <>
      
   
    </>
  );
}
