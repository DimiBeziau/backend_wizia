import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { StyleProvider } from '@ant-design/cssinjs';
import './index.css'
import { RouterProvider } from 'react-router-dom';
import router from './router.jsx';
import { ContextProvider } from './contexts/ContextProvider.jsx';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <StyleProvider layer>
      <ContextProvider>
        <RouterProvider router={router} />
      </ContextProvider>
    </StyleProvider>
  </StrictMode>,
)
