import { createBrowserRouter } from "react-router-dom";
import Login from "./views/Login";
import Signup from "./views/Signup";
import NotFound from "./views/NotFound";
import DefaultLayout from "./components/DefaultLayout";
import GuestLayout from "./components/GuestLayout";
import Dashboard from "./views/Dashboard";
import CalendarPage from "./views/CalendarPage";

const router = createBrowserRouter([
  {
    path: '/',
    element: <DefaultLayout />,
    children: [
      {
        path: '/',
        element: <Dashboard />
      }, {
        path: '/dashboard',
        element: <Dashboard />
      }, {
        path: '/calendar',
        element: <CalendarPage />
      }, {
        path: '/create',
        element: <Dashboard />
      }, {
        path: '/stats',
        element: <Dashboard />
      }
    ]
  }, {
    path: '/',
    element: <GuestLayout />,
    children: [
      {
        path: "/login",
        element: <Login />
      },
      {
        path: '/signup',
        element: <Signup />
      },
    ]
  },
  {
    path: "*",
    element: <NotFound />
  }
])

export default router;