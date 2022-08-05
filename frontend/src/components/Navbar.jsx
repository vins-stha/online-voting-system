import React from 'react';
import HomeIcon from '@material-ui/icons/Home';
import FeaturedPlayListOutlinedIcon from '@material-ui/icons/FeaturedPlayListOutlined';
import AssignmentTurnedInOutlinedIcon from '@material-ui/icons/AssignmentTurnedInOutlined';
import PeopleAltOutlinedIcon from '@material-ui/icons/PeopleAltOutlined';
import NotificationIcon from '@material-ui/icons/Notifications';
import { Avatar } from '@material-ui/core';
import LanguageIcon from '@material-ui/icons/Language';
import SearchIcon from '@material-ui/icons/Search';
import { Button } from '@material-ui/core';
import '../resources/stylessheets/navbar.css'


function Navbar() {
    return (
        <navbar>
            <div className="home_navbar">
                <div className="navbar__logo">
                    <img src="data:image/png;base64,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" alt="" />
                </div>
                <div className="navbar__icons">
                    <div className="navbar__icon">
                        <HomeIcon />
                    </div>
                    <div className="navbar__icon">
                        <FeaturedPlayListOutlinedIcon />
                    </div>
                    <div className="navbar__icon">
                        <AssignmentTurnedInOutlinedIcon />
                    </div>
                    <div className="navbar__icon">
                        <PeopleAltOutlinedIcon />
                    </div>
                    <div className="navbar__icon">
                        <NotificationIcon />
                    </div>

                </div>

                <div className="navbar__right">
                    <div className="navbar__search">

                        <input type="text" className="search" placeholder='Search anything' />
                        <SearchIcon />
                    </div>
                    <div className="navbar__avatar navbar__icon">
                        <LanguageIcon />
                    </div>

                    <div className="navbar__ask">
                        <Button>Ask a question</Button>
                    </div>

                    <div className="navbar__avatar navbar__icon">
                        <Avatar />
                    </div>

                </div>


            </div>
        </navbar>
    )
}

export default Navbar