import { Navigate, Outlet } from "react-router-dom";
import { useStateContext } from "../Context/ContextProvider";

function GuestLayout() {
  const { token } = useStateContext();
  if (token) {
    return <Navigate to="/Dashboard" />
  }
  return (
    <>
      <Outlet />
    </>
  );
}

export default GuestLayout;