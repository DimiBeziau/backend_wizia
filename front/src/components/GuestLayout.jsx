import { Navigate, Outlet } from "react-router-dom";
import { useStateContext } from "../contexts/ContextProvider";

function GuestLayout() {
  const { token } = useStateContext();
  if (token) {
    return (
      <Navigate to="/" />
    )
  }
  return (
    <div className="min-h-screen w-full relative">
      <Outlet />
    </div>
  );
}

export default GuestLayout;