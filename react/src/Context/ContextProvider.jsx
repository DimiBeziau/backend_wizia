// import { createContext, useContext, useState } from "react";

// const StateContext = createContext({
//   user: null,
//   setUser: () => { },
//   token: null,
//   setToken: () => { }
// })

// export const ContextProvider = ({ children }) => {
//   const [user, setUser] = useState({})
//   const [token, _setToken] = useState(
//     localStorage.getItem('ACCESS_TOKEN')
  
//   )

//   const setToken = (token) => {
//     _setToken(token)
//     if (token) {
//       localStorage.setItem('ACCESS_TOKEN', token);
//     } else {
//       localStorage.removeItem('ACCESS_TOKEN');
//     }
//   }

//   console.log(user)
//   console.log(user.id)
//   return (
//     <StateContext.Provider value={{

//       user,
//       setUser,
//       token,
//       setToken
//     }}>
//       {children}
//     </StateContext.Provider>
//   )
// }

// export const useStateContext = () => useContext(StateContext)

import { createContext, useContext, useState, useMemo, useEffect } from "react";

const StateContext = createContext({
  user: null,
  setUser: () => {},
  token: null,
  setToken: () => {},
  userType: null,
});

export const ContextProvider = ({ children }) => {
  const [user, setUser] = useState({});
  const [token, _setToken] = useState(localStorage.getItem("ACCESS_TOKEN"));

  const setToken = (token) => {
    _setToken(token);
    if (token) {
      localStorage.setItem("ACCESS_TOKEN", token);
    } else {
      localStorage.removeItem("ACCESS_TOKEN");
    }
  };


  const userType = useMemo(() => {
    if (!token || typeof token !== "string") return null;
    return token.split("|")[0];
  }, [token]);


  useEffect(() => {
    if (userType) {
      setUser((prevUser) => ({ ...prevUser, id: userType }));
    }
  }, [userType]);

  return (
    <StateContext.Provider
      value={{
        user,
        setUser,
        token,
        setToken,
        userType,
      }}
    >
      {children}
    </StateContext.Provider>
  );
};

export const useStateContext = () => useContext(StateContext);
