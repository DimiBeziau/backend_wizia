import './App.css'
import { ToastContainer } from 'react-toastify';

import { RouterProvider } from 'react-router-dom';
import router from './Rooter/Router';
import { ContextProvider } from './Context/ContextProvider';
function App() {
  return (
    <>
      <ContextProvider>
        <ToastContainer
          Position="top-right" />
        <RouterProvider router={router} />
      </ContextProvider>
    </>
  )
}

export default App