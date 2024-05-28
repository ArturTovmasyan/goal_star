/**
 * Created by andranik on 5/27/15.
 */

module.exports = function(sequelize, DataTypes) {
    return sequelize.define('User', {
            id:                     {type: DataTypes.INTEGER(11), primaryKey: true},
            username:               {type: DataTypes.STRING(255), allowNull: false},
        },
        {
            tableName: 'fos_user',
            timestamps: false,
            underscored: true
        });
};