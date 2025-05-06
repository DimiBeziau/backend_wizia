import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import { loadStripe } from "@stripe/stripe-js";
import './App.css'
import { ToastContainer } from 'react-toastify';

import { RouterProvider } from 'react-router-dom';
import router from './Rooter/Router';
function App() {
  return (
    <>
      <ToastContainer
        Position="top-right" />
      <RouterProvider router={router} />
    </>
  )
}

export default App