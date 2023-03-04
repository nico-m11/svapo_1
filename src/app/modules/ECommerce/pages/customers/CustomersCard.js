import React, { useMemo } from "react";
import {
  Card,
  CardBody,
  CardHeader,
  CardHeaderToolbar,
} from "../../../../../_metronic/_partials/controls";
//import { CustomersFilter } from "./customers-filter/CustomersFilter";
import { CustomersTable } from "./customers-table/CustomersTable";
import { CustomersGrouping } from "./customers-grouping/CustomersGrouping";
import { useCustomersUIContext } from "./CustomersUIContext";

export function CustomersCard() {
  const customersUIContext = useCustomersUIContext();
  const customersUIProps = useMemo(() => {
    return {
      ids: customersUIContext.ids,
      newCustomerButtonClick: customersUIContext.newCustomerButtonClick,
    };
  }, [customersUIContext]);

  return (
    <Card>
      <CardHeader title="Crea la tua lega privata">
        <CardHeaderToolbar>
          {/* <button
            type="button"
            className="btn btn-primary"
            //onClick={customersUIProps.newCustomerButtonClick}
          >
            New Customer
          </button> */}
        </CardHeaderToolbar>
      </CardHeader>
      <CardBody>
        {/* <CustomersFilter /> */}
        <CustomersTable />
      </CardBody>
    </Card>
  );
}
