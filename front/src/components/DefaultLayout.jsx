import { useEffect, useState } from "react";
import { useStateContext } from "../contexts/ContextProvider";
import { Navigate, Outlet, useLocation } from "react-router-dom";
import Footer from "./Footer";

function DefaultLayout() {
  const { user, token } = useStateContext();
  const [route, setRoute] = useState('');
  const location = useLocation(); // pour détecter les changements de route

  const pathToPage = {
    '/': 'dashboard',
    '/dashboard': 'dashboard',
    '/calendar': 'calendar',
    '/create': 'create',
    '/stats': 'stats',
  };

  useEffect(() => {
    setRoute(pathToPage[location.pathname] || '');
  }, [location.pathname]);


  if (!token) {
    return (
      <Navigate to='/login' />
    )
  }
  return (
    <div className="min-h-screen w-full relative">
      <Outlet />
      <Footer route={route} />
    </div>
  );
}

export default DefaultLayout;