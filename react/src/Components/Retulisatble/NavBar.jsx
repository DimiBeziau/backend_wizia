import "./Style/NavBar.css"
import { NavLink } from "react-router-dom";

const NavBar = () => {
    
    return (
        <nav className="navbar">
            <NavLink to="/" className="nav-link" activeClassName="active-link">
            <div className="navbar-logo">
                <h1> WIZIA</h1>
            </div>
            </NavLink>
            <ul className="navbar-links">
                <li>
                    <NavLink to="/Dashboard" className="nav-link" activeClassName="active-link">
                    Welcolm
                    </NavLink>   
                </li>
                <li>
                    <NavLink to="/Dashboard/Réseaux_Sociaux" className="nav-link" activeClassName="active-link">
                    Réseaux sociaux
                    </NavLink>   
                </li>
                <li>
                    <NavLink to="/Dashboard/Newsletters" className="nav-link" activeClassName="active-link">
                    Newsletters
                    </NavLink>   
                </li>
                 <li className="dropdown">
                 <a href="#" className="nav-link dropbtn">
                 <img src="/icons/anchor.png"  style={{ width: "16px", marginRight: "8px" }} />
                       
                     </a>
                <div className="dropdown-content">
            <NavLink to="/Dashboard/Abonnement" className="nav-link">Abonnement</NavLink>
            <NavLink to="/Dashboard/UpdateProfil" className="nav-link">Profil</NavLink>
            <NavLink to="/Dashboard/History" className="nav-link">Historique</NavLink>
          </div>
        </li>
            </ul>
        </nav>

    )
  };
  
  export default NavBar;