# Wahlenraten

A simple panel to collect estimates for election outcomes and compare them
with eachother.

Currently only localized to Germany, contains very few localizable texts though.

## Requirements

* An HTTPd with PHP and the SQLite PDO connector

## Setup

* Clone the repository into a directory served by the HTTPd
* Move or otherwise prevent access to `wahl.db3` by remote users (or don't)
* Make sure the user running the HTTPd has read and write access to `wahl.db3` *AND* the folder containing it
