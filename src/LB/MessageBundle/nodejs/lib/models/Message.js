/**
 * Created by andranik on 5/27/15.
 */

module.exports = function(sequelize, DataTypes) {
    return sequelize.define('Message', {
            id:                     {type: DataTypes.INTEGER(11), primaryKey: true},
            from_user_id:           {type: DataTypes.INTEGER(11), allowNull: false},
            to_user_id:             {type: DataTypes.INTEGER(11), allowNull: false},
            subject:                {type: DataTypes.STRING(50), allowNull: false},
            content:                {type: DataTypes.STRING(100), allowNull: false},
            is_read:                {type: DataTypes.INTEGER(1), allowNull: false},
            is_deleted:             {type: DataTypes.INTEGER(1), allowNull: false},
            created:                {type: DataTypes.DATE, allowNull: false}
        },
        {
            tableName: 'message',
            timestamps: false,
            underscored: true
        });
};