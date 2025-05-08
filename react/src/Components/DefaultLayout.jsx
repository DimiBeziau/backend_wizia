import { Navigate, Outlet } from "react-router-dom";
import { useStateContext } from "../Context/ContextProvider";

function DefaultLayout() {
  const { user, token } = useStateContext();
  if (!token) {
    return <Navigate to="/login" />
  }
  return (
    <>
      <Outlet />
    </>
  );
}

export default DefaultLayout;