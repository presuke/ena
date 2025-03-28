#!/bin/bash
cd /home/reno/Desktop/ena/client/
# 現在の日時を取得
current_date=$(date '+%Y-%m-%d %H:%M:%S')

echo "[$current_date] run run.sh" >> run.log
python ena.py
