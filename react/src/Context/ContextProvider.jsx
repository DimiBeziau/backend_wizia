import { createContext, useContext, useState } from "react";

const StateContext = createContext({
  user: null,
  setUser: () => { },
  token: null,
  setToken: () => { }
})

export const ContextProvider = ({ children }) => {
  const [user, setUser] = useState({})
  const [token, _setToken] = useState(
    localStorage.getItem('ACCESS_TOKEN')
  
  )

  const setToken = (token) => {
    _setToken(token)
    if (token) {
      localStorage.setItem('ACCESS_TOKEN', token);
    } else {
      localStorage.removeItem('ACCESS_TOKEN');
    }
  }

  console.log(user)
  console.log(user.id )

  return (
    <StateContext.Provider value={{

      user,
      setUser,
      token,
      setToken
    }}>
      {children}
    </StateContext.Provider>
  )
}

export const useStateContext = () => useContext(StateContext)


