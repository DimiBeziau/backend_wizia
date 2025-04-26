import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'
import { ToastContainer } from 'react-toastify';

import Root from "./Rooter/Root"
function App() {
  return (
    <>
      <ToastContainer
        Position="top-right" />
      <Root />
    </>
  )
}

export default App